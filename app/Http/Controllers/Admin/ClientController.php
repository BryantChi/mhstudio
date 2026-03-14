<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * 客戶列表
     */
    public function index(Request $request): View
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * 新增客戶表單
     */
    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.clients.create', compact('users'));
    }

    /**
     * 儲存新客戶
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'source' => 'required|in:website,referral,social,cold_outreach,other',
            'status' => 'required|in:lead,active,inactive,archived',
            'tier' => 'required|in:standard,premium,vip',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // 處理標籤
        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        Client::create($validated);
        flash_success('客戶建立成功');

        return redirect()->route('admin.clients.index');
    }

    /**
     * 客戶詳情
     */
    public function show(Client $client): View
    {
        $client->load([
            'interactions' => fn ($q) => $q->with('user')->latest('interaction_date'),
            'contracts' => fn ($q) => $q->latest(),
            'quotes' => fn ($q) => $q->latest(),
            'invoices' => fn ($q) => $q->latest(),
        ]);

        return view('admin.clients.show', compact('client'));
    }

    /**
     * 編輯客戶表單
     */
    public function edit(Client $client): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.clients.edit', compact('client', 'users'));
    }

    /**
     * 更新客戶
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'source' => 'required|in:website,referral,social,cold_outreach,other',
            'status' => 'required|in:lead,active,inactive,archived',
            'tier' => 'required|in:standard,premium,vip',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        $client->update($validated);
        flash_success('客戶更新成功');

        return redirect()->route('admin.clients.index');
    }

    /**
     * 刪除客戶
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();
        flash_success('客戶已刪除');

        return redirect()->route('admin.clients.index');
    }

    /**
     * 新增互動紀錄
     */
    public function storeInteraction(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:note,call,email,meeting,other',
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
            'interaction_date' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();

        $client->interactions()->create($validated);
        flash_success('互動紀錄已新增');

        return redirect()->route('admin.clients.show', $client);
    }

    /**
     * 刪除互動紀錄
     */
    public function destroyInteraction(ClientInteraction $interaction): RedirectResponse
    {
        $clientId = $interaction->client_id;
        $interaction->delete();
        flash_success('互動紀錄已刪除');

        return redirect()->route('admin.clients.show', $clientId);
    }
}
