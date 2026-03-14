<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * 發票列表
     */
    public function index(Request $request): View
    {
        $query = Invoice::with(['client', 'project']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // 統計卡片
        $stats = [
            'total_revenue' => Invoice::paid()->sum('total'),
            'month_revenue' => Invoice::paid()->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total'),
            'pending_amount' => Invoice::unpaid()->sum('total') - Invoice::unpaid()->sum('paid_amount'),
            'overdue_count' => Invoice::overdue()->count(),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * 新增發票表單
     */
    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();
        $selectedClientId = $request->get('client_id');

        return view('admin.invoices.create', compact('clients', 'projects', 'selectedClientId'));
    }

    /**
     * 儲存新發票
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'status' => 'required|in:draft,sent,paid,partially_paid,overdue,cancelled',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'status' => $validated['status'],
            'tax_rate' => $validated['tax_rate'],
            'discount' => $validated['discount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'issued_date' => $validated['issued_date'],
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? '項',
                'unit_price' => $item['unit_price'],
                'amount' => round($item['quantity'] * $item['unit_price'], 2),
                'order' => $index,
            ]);
        }

        $invoice->recalculate();

        flash_success('發票建立成功');

        return redirect()->route('admin.invoices.index');
    }

    /**
     * 發票詳情
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['client', 'project', 'creator', 'items', 'quote']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * 編輯發票表單
     */
    public function edit(Invoice $invoice): View
    {
        $invoice->load('items');
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();

        return view('admin.invoices.edit', compact('invoice', 'clients', 'projects'));
    }

    /**
     * 更新發票
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'status' => 'required|in:draft,sent,paid,partially_paid,overdue,cancelled',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'status' => $validated['status'],
            'tax_rate' => $validated['tax_rate'],
            'discount' => $validated['discount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'issued_date' => $validated['issued_date'],
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $invoice->items()->delete();
        foreach ($validated['items'] as $index => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? '項',
                'unit_price' => $item['unit_price'],
                'amount' => round($item['quantity'] * $item['unit_price'], 2),
                'order' => $index,
            ]);
        }

        $invoice->recalculate();

        flash_success('發票更新成功');

        return redirect()->route('admin.invoices.index');
    }

    /**
     * 刪除發票
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();
        flash_success('發票已刪除');

        return redirect()->route('admin.invoices.index');
    }

    /**
     * 記錄付款
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'nullable|string|max:255',
        ]);

        $invoice->recordPayment($request->amount, $request->payment_method);
        flash_success('付款已記錄');

        return redirect()->route('admin.invoices.show', $invoice);
    }
}
