<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ContractController extends Controller
{
    /**
     * 合約列表
     */
    public function index(Request $request): View
    {
        $query = Contract::with(['client', 'project']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('contract_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $contracts = $query->latest()->paginate(15)->withQueryString();

        // 即將到期合約
        $expiringSoon = Contract::expiringSoon()->with('client')->get();

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('admin.contracts.index', compact('contracts', 'expiringSoon', 'clients'));
    }

    /**
     * 新增合約表單
     */
    public function create(Request $request): View
    {
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();
        $templates = ContractTemplate::active()->ordered()->get();

        // 服務方案（供「從服務方案快速建立」面板帶入項目）
        $servicePlans = Service::active()->ordered()
            ->whereNotNull('type')
            ->with(['items' => fn ($q) => $q->active()->ordered()])
            ->get()
            ->groupBy('type');

        // 從範本建立
        $selectedTemplate = null;
        if ($request->filled('template_id')) {
            $selectedTemplate = ContractTemplate::find($request->template_id);
        }

        // 預選客戶
        $selectedClientId = $request->get('client_id');

        return view('admin.contracts.create', compact(
            'clients',
            'projects',
            'templates',
            'servicePlans',
            'selectedTemplate',
            'selectedClientId'
        ));
    }

    /**
     * 儲存新合約
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:service,maintenance,retainer,nda,other',
            'status' => 'required|in:draft,sent,signed,active,completed,cancelled',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            // 財務明細
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            // 付款追蹤
            'payment_terms' => 'nullable|in:due_on_signing,net15,net30,net60,milestone,custom',
            'payment_method' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            // 續約與保固
            'auto_renew' => 'nullable|boolean',
            'renewal_notice_days' => 'nullable|integer|min:1',
            'warranty_months' => 'nullable|integer|min:0',
            'ip_ownership' => 'nullable|in:client,shared,studio',
            // 簽署方
            'client_signer_name' => 'nullable|string|max:255',
            'client_signer_title' => 'nullable|string|max:255',
            'client_signer_email' => 'nullable|email|max:255',
            'company_signer_name' => 'nullable|string|max:255',
            'execution_method' => 'nullable|in:wet_ink,esignature,email_consent',
            // 項目
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $contract = Contract::create([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'amount' => $validated['amount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'tax_rate' => $validated['tax_rate'] ?? 5,
            'discount' => $validated['discount'] ?? 0,
            'payment_terms' => $validated['payment_terms'] ?? 'net30',
            'payment_method' => $validated['payment_method'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'auto_renew' => $validated['auto_renew'] ?? false,
            'renewal_notice_days' => $validated['renewal_notice_days'] ?? 30,
            'warranty_months' => $validated['warranty_months'] ?? null,
            'ip_ownership' => $validated['ip_ownership'] ?? 'client',
            'client_signer_name' => $validated['client_signer_name'] ?? null,
            'client_signer_title' => $validated['client_signer_title'] ?? null,
            'client_signer_email' => $validated['client_signer_email'] ?? null,
            'company_signer_name' => $validated['company_signer_name'] ?? null,
            'execution_method' => $validated['execution_method'] ?? 'wet_ink',
        ]);

        // 建立項目
        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $index => $item) {
                $contract->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? '項',
                    'unit_price' => $item['unit_price'],
                    'amount' => round($item['quantity'] * $item['unit_price'], 2),
                    'order' => $index,
                ]);
            }

            // 重新計算金額
            $contract->recalculate();
        }

        // 以合約資料填入正文佔位符
        $contract->applyContentPlaceholders();

        flash_success('合約建立成功');

        return redirect(admin_list_url('admin.contracts.index'));
    }

    /**
     * 合約詳情
     */
    public function show(Contract $contract): View
    {
        $contract->load(['client', 'project', 'creator', 'items', 'quote', 'payments']);

        // 活動紀錄
        $activities = Activity::where('subject_type', Contract::class)
            ->where('subject_id', $contract->id)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.contracts.show', compact('contract', 'activities'));
    }

    /**
     * 編輯合約表單
     */
    public function edit(Contract $contract): View
    {
        $contract->load('items');
        $clients = Client::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();

        return view('admin.contracts.edit', compact('contract', 'clients', 'projects'));
    }

    /**
     * 更新合約
     */
    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:service,maintenance,retainer,nda,other',
            'status' => 'required|in:draft,sent,signed,active,completed,cancelled',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'signed_at' => 'nullable|date',
            'notes' => 'nullable|string',
            // 財務明細
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            // 付款追蹤
            'payment_terms' => 'nullable|in:due_on_signing,net15,net30,net60,milestone,custom',
            'payment_method' => 'nullable|string|max:255',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            // 續約與保固
            'auto_renew' => 'nullable|boolean',
            'renewal_notice_days' => 'nullable|integer|min:1',
            'warranty_months' => 'nullable|integer|min:0',
            'ip_ownership' => 'nullable|in:client,shared,studio',
            // 簽署方
            'client_signer_name' => 'nullable|string|max:255',
            'client_signer_title' => 'nullable|string|max:255',
            'client_signer_email' => 'nullable|email|max:255',
            'company_signer_name' => 'nullable|string|max:255',
            'execution_method' => 'nullable|in:wet_ink,esignature,email_consent',
            // 項目
            'items' => 'nullable|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $contract->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'] ?? null,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'amount' => $validated['amount'] ?? 0,
            'currency' => $validated['currency'] ?? 'TWD',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'signed_at' => $validated['signed_at'] ?? $contract->signed_at,
            'notes' => $validated['notes'] ?? null,
            'tax_rate' => $validated['tax_rate'] ?? 5,
            'discount' => $validated['discount'] ?? 0,
            'payment_terms' => $validated['payment_terms'] ?? 'net30',
            'payment_method' => $validated['payment_method'] ?? null,
            'paid_amount' => $validated['paid_amount'] ?? $contract->paid_amount,
            'due_date' => $validated['due_date'] ?? null,
            'auto_renew' => $validated['auto_renew'] ?? false,
            'renewal_notice_days' => $validated['renewal_notice_days'] ?? 30,
            'warranty_months' => $validated['warranty_months'] ?? null,
            'ip_ownership' => $validated['ip_ownership'] ?? 'client',
            'client_signer_name' => $validated['client_signer_name'] ?? null,
            'client_signer_title' => $validated['client_signer_title'] ?? null,
            'client_signer_email' => $validated['client_signer_email'] ?? null,
            'company_signer_name' => $validated['company_signer_name'] ?? null,
            'execution_method' => $validated['execution_method'] ?? 'wet_ink',
        ]);

        // 刪除舊項目並重建
        if (! empty($validated['items'])) {
            $contract->items()->delete();
            foreach ($validated['items'] as $index => $item) {
                $contract->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? '項',
                    'unit_price' => $item['unit_price'],
                    'amount' => round($item['quantity'] * $item['unit_price'], 2),
                    'order' => $index,
                ]);
            }

            $contract->recalculate();
        }

        // 以合約資料填入正文佔位符
        $contract->applyContentPlaceholders();

        flash_success('合約更新成功');

        return redirect(admin_list_url('admin.contracts.index'));
    }

    /**
     * 刪除合約
     */
    public function destroy(Contract $contract): RedirectResponse
    {
        // 刪除客戶回簽檔（避免遺留孤兒檔案）；收款帳本由 HasPayments 連帶刪除
        if ($contract->signed_document_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($contract->signed_document_path);
        }

        $contract->delete();
        flash_success('合約已刪除');

        return redirect(admin_list_url('admin.contracts.index'));
    }

    /**
     * 更新合約狀態
     */
    public function updateStatus(Request $request, Contract $contract): RedirectResponse
    {
        // 簽署（signed）僅能透過上傳客戶回簽檔達成，不開放此處直接設定
        $request->validate([
            'status' => 'required|in:draft,sent,active,completed,cancelled',
        ]);

        if (! $contract->canTransitionTo($request->status)) {
            flash_error("無法從「{$contract->status_label}」轉為該狀態");

            return redirect()->route('admin.contracts.show', $contract);
        }

        $updateData = ['status' => $request->status];
        if ($request->status === 'sent' && ! $contract->sent_at) {
            $updateData['sent_at'] = now();
        }

        $contract->update($updateData);
        flash_success('合約狀態已更新');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 標記為已送出（使用者自行下載 PDF 寄送給客戶）
     */
    public function markAsSent(Contract $contract): RedirectResponse
    {
        if ($contract->status !== 'sent' && ! $contract->canTransitionTo('sent')) {
            flash_error('目前狀態無法標記為已送出');

            return redirect()->route('admin.contracts.show', $contract);
        }

        $contract->update([
            'status' => 'sent',
            'sent_at' => $contract->sent_at ?: now(),
        ]);
        flash_success('合約已標記為已送出');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 將合約 PDF 以 Email 寄給客戶
     */
    public function emailToClient(Contract $contract): RedirectResponse
    {
        $email = $contract->client_signer_email ?: $contract->client->email;
        if (! $email) {
            flash_error('找不到客戶 Email，請先於合約簽署人或客戶資料填寫');

            return redirect()->route('admin.contracts.show', $contract);
        }

        \Illuminate\Support\Facades\Mail::to($email)->send(
            new \App\Mail\ContractToClient($contract)
        );

        $contract->update([
            'status' => $contract->canTransitionTo('sent') ? 'sent' : $contract->status,
            'sent_at' => $contract->sent_at ?: now(),
        ]);
        flash_success("合約已寄送至 {$email}");

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 上傳客戶回簽合約 → 標記為已簽署
     */
    public function uploadSignedDocument(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate([
            'signed_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if (in_array($contract->status, ['completed', 'cancelled'], true)) {
            flash_error('此合約狀態無法標記為已簽署');

            return redirect()->route('admin.contracts.show', $contract);
        }

        $data = [
            'signed_at' => $contract->signed_at ?: now(),
            'status' => 'signed',
        ];

        // 紙本簽署可不附檔；若有客戶回簽 PDF／掃描圖則一併保存
        if ($request->hasFile('signed_document')) {
            $file = $request->file('signed_document');
            $filename = \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension();
            $data['signed_document_path'] = $file->storeAs('uploads/'.date('Y/m'), $filename, 'public');
            $data['signed_document_uploaded_at'] = now();
        }

        $contract->update($data);
        flash_success($request->hasFile('signed_document')
            ? '已上傳客戶回簽合約，狀態更新為「已簽署」'
            : '合約已標記為「已簽署」');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 登記一筆收款
     */
    public function recordPayment(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$contract->balance_due,
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

        $contract->recordPayment(
            (float) $validated['amount'],
            $validated['payment_method'] ?? null,
            $validated['paid_on'] ?? null,
            $validated['note'] ?? null,
            $proofPath,
        );
        flash_success('收款已登記');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 刪除一筆收款並重算已收金額
     */
    public function destroyPayment(Contract $contract, Payment $payment): RedirectResponse
    {
        if ($payment->payable_type !== Contract::class || (int) $payment->payable_id !== $contract->id) {
            abort(404);
        }

        if ($payment->proof_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($payment->proof_path);
        }

        $payment->delete();
        $contract->syncPaidAmount();
        flash_success('收款紀錄已刪除');

        return redirect()->route('admin.contracts.show', $contract);
    }

    /**
     * 複製合約
     */
    public function duplicate(Contract $contract): RedirectResponse
    {
        $contract->load('items');

        $newContract = $contract->replicate();
        $newContract->title = $contract->title.' (複本)';
        $newContract->status = 'draft';
        $newContract->contract_number = null; // Will auto-generate
        $newContract->quote_id = null; // 複本不沿用來源報價單關聯
        $newContract->signed_at = null;
        $newContract->sent_at = null;
        $newContract->signed_document_path = null; // 不沿用原合約的客戶回簽檔
        $newContract->signed_document_uploaded_at = null;
        $newContract->start_date = null; // 複本重新設定合約期間
        $newContract->end_date = null;
        $newContract->paid_at = null;
        $newContract->paid_amount = 0;
        $newContract->created_by = auth()->id();
        $newContract->save();

        // 複製項目
        foreach ($contract->items as $item) {
            $newItem = $item->replicate();
            $newItem->contract_id = $newContract->id;
            $newItem->save();
        }

        // 重新計算
        $newContract->recalculate();

        flash_success('合約已複製');

        return redirect()->route('admin.contracts.edit', $newContract);
    }

    /**
     * 匯出 PDF
     */
    public function exportPdf(Contract $contract)
    {
        $contract->load(['client', 'project', 'creator', 'items']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.contracts.pdf', compact('contract'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($contract->contract_number.'.pdf');
    }
}
