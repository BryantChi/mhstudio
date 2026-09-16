<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

        // 暫定客戶篩選：only=僅暫定、exclude=僅正式、未帶=全部
        if ($request->provisional === 'only') {
            $query->provisional();
        } elseif ($request->provisional === 'exclude') {
            $query->formal();
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
            'tax_id' => 'nullable|string|max:20',
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

        return redirect(admin_list_url('admin.clients.index'));
    }

    /**
     * 表單內快速建立客戶（AJAX）。
     * 不重用 store() 的原因：store() 強制要求 source/status/tier，且回傳 redirect，AJAX 接不到。
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:20',
        ]);

        $client = Client::create($validated + [
            'status' => 'lead',
            'source' => 'other',
            'tier' => 'standard',
            'is_provisional' => $request->boolean('is_provisional', true),
        ]);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'company' => $client->company,
            'is_provisional' => $client->is_provisional,
        ]);
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
            'tax_id' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'source' => 'required|in:website,referral,social,cold_outreach,other',
            'status' => 'required|in:lead,active,inactive,archived',
            'tier' => 'required|in:standard,premium,vip',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'is_provisional' => 'nullable|boolean',
        ]);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // checkbox 未勾選時不會送出，需明確補 false，否則無法藉由編輯頁轉正
        $validated['is_provisional'] = $request->boolean('is_provisional');

        $client->update($validated);
        flash_success('客戶更新成功');

        return redirect(admin_list_url('admin.clients.index'));
    }

    /**
     * 暫定客戶轉為正式客戶
     */
    public function promote(Client $client): RedirectResponse
    {
        $client->update(['is_provisional' => false]);
        flash_success('已轉為正式客戶');

        return redirect()->route('admin.clients.show', $client);
    }

    /**
     * 刪除客戶
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();
        flash_success('客戶已刪除');

        return redirect(admin_list_url('admin.clients.index'));
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
