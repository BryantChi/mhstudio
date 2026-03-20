<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractTemplateController extends Controller
{
    use ReordersItems;
    /**
     * 範本列表
     */
    public function index(Request $request): View|JsonResponse
    {
        // 排序模式：回傳精簡 JSON 資料
        if ($request->has('_sortable')) {
            $items = ContractTemplate::ordered()->get(['id', 'name', 'order']);
            return response()->json($items);
        }

        $query = ContractTemplate::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $templates = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.contract-templates.index', compact('templates'));
    }

    /**
     * 新增範本表單
     */
    public function create(): View
    {
        return view('admin.contract-templates.create');
    }

    /**
     * 儲存範本
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:service,maintenance,retainer,nda,other',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'default_amount' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? ContractTemplate::max('order') + 1;

        $contractTemplate = ContractTemplate::create($validated);
        $this->syncOrder(ContractTemplate::class, $contractTemplate->id, $contractTemplate->order);

        flash_success('合約範本建立成功');

        return redirect(admin_list_url('admin.contract-templates.index'));
    }

    /**
     * 編輯範本
     */
    public function edit(ContractTemplate $contractTemplate): View
    {
        return view('admin.contract-templates.edit', compact('contractTemplate'));
    }

    /**
     * 更新範本
     */
    public function update(Request $request, ContractTemplate $contractTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:service,maintenance,retainer,nda,other',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'default_amount' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $contractTemplate->update($validated);
        $this->syncOrder(ContractTemplate::class, $contractTemplate->id, $contractTemplate->order);

        flash_success('合約範本更新成功');

        return redirect(admin_list_url('admin.contract-templates.index'));
    }

    /**
     * 刪除範本
     */
    public function destroy(ContractTemplate $contractTemplate): RedirectResponse
    {
        $contractTemplate->delete();

        flash_success('合約範本已刪除');

        return redirect(admin_list_url('admin.contract-templates.index'));
    }

    /**
     * AJAX 拖曳排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:contract_templates,id',
        ]);

        foreach ($request->ids as $index => $id) {
            ContractTemplate::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
