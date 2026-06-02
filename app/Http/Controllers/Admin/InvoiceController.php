<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

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
            // 現金基礎：依實際收款日 paid_on 加總實收金額（含部分付款），與 Dashboard 口徑一致
            'total_revenue' => (float) Payment::forInvoices()->sum('amount'),
            'year_revenue' => (float) Payment::forInvoices()->inYear(now()->year)->sum('amount'),
            'month_revenue' => (float) Payment::forInvoices()->inMonth(now()->month, now()->year)->sum('amount'),
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

        return redirect(admin_list_url('admin.invoices.index'));
    }

    /**
     * 發票詳情
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['client', 'project', 'creator', 'items', 'quote', 'payments', 'contract']);

        $activities = Activity::where('subject_type', Invoice::class)
            ->where('subject_id', $invoice->id)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.invoices.show', compact('invoice', 'activities'));
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

        return redirect(admin_list_url('admin.invoices.index'));
    }

    /**
     * 刪除發票
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();
        flash_success('發票已刪除');

        return redirect(admin_list_url('admin.invoices.index'));
    }

    /**
     * 記錄付款（發票自身帳本）。所有發票（含合約發票）一律走此帳本，合約 paid_amount 由其發票衍生。
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$invoice->balance_due,
            'payment_method' => 'nullable|string|max:255',
            'paid_on' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // 收款憑證（轉帳截圖／收據，選填）
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $proofPath = $file->storeAs('uploads/'.date('Y/m'), \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        $invoice->recordPayment($request->amount, $request->payment_method, $request->paid_on, $request->note, $proofPath);
        flash_success('付款已記錄');

        return redirect()->route('admin.invoices.show', $invoice);
    }

    /**
     * 編輯一筆收款並重算帳本（修正填錯的金額／收款日／方式／備註，可換憑證）。
     */
    public function updatePayment(Request $request, Invoice $invoice, Payment $payment): RedirectResponse
    {
        if ($payment->payable_type !== Invoice::class || (int) $payment->payable_id !== $invoice->id) {
            abort(404);
        }

        // 上限為「釋放本筆後的可用額度」：目前餘額已扣掉本筆，需加回本筆原金額
        $maxAmount = round($invoice->balance_due + (float) $payment->amount, 2);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$maxAmount,
            'payment_method' => 'nullable|string|max:255',
            'paid_on' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // 換憑證：先刪舊檔再存新檔；未上傳則保留原憑證
        $proofPath = $payment->proof_path;
        if ($request->hasFile('proof')) {
            if ($payment->proof_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($payment->proof_path);
            }
            $file = $request->file('proof');
            $proofPath = $file->storeAs('uploads/'.date('Y/m'), \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        $payment->update([
            'amount' => round((float) $request->amount, 2),
            'payment_method' => $request->payment_method,
            'paid_on' => $request->paid_on ?: $payment->paid_on->toDateString(),
            'note' => $request->note,
            'proof_path' => $proofPath,
        ]);

        $invoice->syncPaidAmount();
        flash_success('收款紀錄已更新');

        return redirect()->route('admin.invoices.show', $invoice);
    }

    /**
     * 刪除一筆收款並重算
     */
    public function destroyPayment(Invoice $invoice, Payment $payment): RedirectResponse
    {
        if ($payment->payable_type !== Invoice::class || (int) $payment->payable_id !== $invoice->id) {
            abort(404);
        }

        if ($payment->proof_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($payment->proof_path);
        }

        $payment->delete();
        $invoice->syncPaidAmount();
        flash_success('收款紀錄已刪除');

        return redirect()->route('admin.invoices.show', $invoice);
    }
}
