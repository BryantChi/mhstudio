<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    /**
     * 報價單列表
     */
    public function index(Request $request): View
    {
        $query = Quote::with(['client', 'project']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotes = $query->latest()->paginate(15)->withQueryString();

        return view('admin.quotes.index', compact('quotes'));
    }

    /**
     * 新增報價單表單
     */
    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();
        $selectedClientId = $request->get('client_id');

        $servicePlans = Service::active()->ordered()
            ->whereNotNull('type')
            ->with(['items' => fn ($q) => $q->active()->ordered()])
            ->get()
            ->groupBy('type');

        return view('admin.quotes.create', compact('clients', 'projects', 'selectedClientId', 'servicePlans'));
    }

    /**
     * 儲存新報價單
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quote = Quote::create([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'tax_rate' => $validated['tax_rate'],
            'discount' => $validated['discount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'valid_until' => $validated['valid_until'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // 建立項目
        foreach ($validated['items'] as $index => $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? '項',
                'unit_price' => $item['unit_price'],
                'amount' => round($item['quantity'] * $item['unit_price'], 2),
                'order' => $index,
            ]);
        }

        // 重新計算金額
        $quote->recalculate();

        flash_success('報價單建立成功');

        return redirect(admin_list_url('admin.quotes.index'));
    }

    /**
     * 報價單詳情
     */
    public function show(Quote $quote): View
    {
        $quote->load(['client', 'project', 'creator', 'items', 'invoice', 'contract']);

        return view('admin.quotes.show', compact('quote'));
    }

    /**
     * 編輯報價單表單
     */
    public function edit(Quote $quote): View
    {
        $quote->load('items');
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();

        return view('admin.quotes.edit', compact('quote', 'clients', 'projects'));
    }

    /**
     * 更新報價單
     */
    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quote->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'tax_rate' => $validated['tax_rate'],
            'discount' => $validated['discount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'valid_until' => $validated['valid_until'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // 刪除舊項目並重建
        $quote->items()->delete();
        foreach ($validated['items'] as $index => $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? '項',
                'unit_price' => $item['unit_price'],
                'amount' => round($item['quantity'] * $item['unit_price'], 2),
                'order' => $index,
            ]);
        }

        $quote->recalculate();

        flash_success('報價單更新成功');

        return redirect(admin_list_url('admin.quotes.index'));
    }

    /**
     * 刪除報價單
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();
        flash_success('報價單已刪除');

        return redirect(admin_list_url('admin.quotes.index'));
    }

    /**
     * 匯出報價單 PDF
     */
    public function exportPdf(Quote $quote)
    {
        $quote->load(['client', 'project', 'creator', 'items']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.quotes.pdf', compact('quote'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($quote->quote_number . '.pdf');
    }

    /**
     * 複製報價單
     */
    public function duplicate(Quote $quote): RedirectResponse
    {
        $newQuote = $quote->replicate();
        $newQuote->title = $quote->title.' (複本)';
        $newQuote->status = 'draft';
        $newQuote->quote_number = null; // Will auto-generate
        $newQuote->valid_until = now()->addDays(30);
        $newQuote->created_by = auth()->id();
        $newQuote->save();

        // 複製項目
        foreach ($quote->items as $item) {
            $newItem = $item->replicate();
            $newItem->quote_id = $newQuote->id;
            $newItem->save();
        }

        // 重新計算
        $newQuote->recalculate();

        flash_success('報價單已複製');

        return redirect()->route('admin.quotes.edit', $newQuote);
    }

    /**
     * 將報價單轉換為合約
     */
    public function convertToContract(Quote $quote): RedirectResponse
    {
        // 檢查是否已轉換
        if ($quote->contract) {
            flash_error('此報價單已轉換為合約');

            return redirect()->route('admin.quotes.show', $quote);
        }

        $quote->load('items');

        // 建立合約
        $contract = Contract::create([
            'client_id' => $quote->client_id,
            'project_id' => $quote->project_id,
            'quote_id' => $quote->id,
            'title' => $quote->title.' — 合約',
            'content' => "依據報價單 {$quote->quote_number} 之內容，雙方同意以下合約條款。",
            'type' => 'service',
            'status' => 'draft',
            'subtotal' => $quote->subtotal,
            'tax_rate' => $quote->tax_rate,
            'tax_amount' => $quote->tax_amount,
            'discount' => $quote->discount,
            'total' => $quote->total,
            'amount' => $quote->total,
            'currency' => $quote->currency,
            'start_date' => now(),
            'notes' => '來源報價單：'.$quote->quote_number,
        ]);

        // 複製項目
        foreach ($quote->items as $item) {
            ContractItem::create([
                'contract_id' => $contract->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'order' => $item->order,
            ]);
        }

        flash_success('已從報價單建立合約草稿');

        return redirect()->route('admin.contracts.edit', $contract);
    }

    /**
     * 將報價單轉換為發票
     */
    public function convertToInvoice(Quote $quote): RedirectResponse
    {
        // 檢查是否已轉換
        if ($quote->invoice) {
            flash_error('此報價單已轉換為發票');

            return redirect()->route('admin.quotes.show', $quote);
        }

        $quote->load('items');

        // 建立發票
        $invoice = Invoice::create([
            'client_id' => $quote->client_id,
            'project_id' => $quote->project_id,
            'quote_id' => $quote->id,
            'title' => $quote->title,
            'status' => 'draft',
            'subtotal' => $quote->subtotal,
            'tax_rate' => $quote->tax_rate,
            'tax_amount' => $quote->tax_amount,
            'discount' => $quote->discount,
            'total' => $quote->total,
            'currency' => $quote->currency,
            'issued_date' => now(),
            'due_date' => now()->addDays(30),
            'notes' => $quote->notes,
        ]);

        // 複製項目
        foreach ($quote->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'order' => $item->order,
            ]);
        }

        // 更新報價單狀態
        $quote->update(['status' => 'accepted', 'accepted_at' => now()]);

        flash_success('已成功將報價單轉換為發票');

        return redirect()->route('admin.invoices.show', $invoice);
    }
}
