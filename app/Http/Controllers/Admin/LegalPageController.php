<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    use ReordersItems;
    /**
     * 法律頁面列表
     */
    public function index(Request $request): View|JsonResponse
    {
        // 排序模式：回傳精簡 JSON 資料
        if ($request->has('_sortable')) {
            $items = LegalPage::ordered()->get(['id', 'title', 'order']);
            return response()->json($items);
        }

        $query = LegalPage::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $pages = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.legal-pages.index', compact('pages'));
    }

    /**
     * 新增表單
     */
    public function create(): View
    {
        $types = LegalPage::TYPES;

        return view('admin.legal-pages.create', compact('types'));
    }

    /**
     * 儲存
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages',
            'type' => 'required|in:' . implode(',', array_keys(LegalPage::TYPES)),
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        // 表單送出 1-based，轉為 0-based 儲存
        $validated['order'] = isset($validated['order']) ? max(0, $validated['order'] - 1) : (LegalPage::max('order') ?? -1) + 1;

        $legalPage = LegalPage::create($validated);
        $this->syncOrder(LegalPage::class, $legalPage->id, $legalPage->order);

        flash_success('法律頁面建立成功');

        return redirect(admin_list_url('admin.legal-pages.index'));
    }

    /**
     * 編輯表單
     */
    public function edit(LegalPage $legalPage): View
    {
        $types = LegalPage::TYPES;

        return view('admin.legal-pages.edit', compact('legalPage', 'types'));
    }

    /**
     * 更新
     */
    public function update(Request $request, LegalPage $legalPage): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages,slug,' . $legalPage->id,
            'type' => 'required|in:' . implode(',', array_keys(LegalPage::TYPES)),
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // 表單送出 1-based，轉為 0-based 儲存
        if (isset($validated['order'])) {
            $validated['order'] = max(0, $validated['order'] - 1);
        }

        $legalPage->update($validated);
        $this->syncOrder(LegalPage::class, $legalPage->id, $legalPage->order);

        flash_success('法律頁面更新成功');

        return redirect(admin_list_url('admin.legal-pages.index'));
    }

    /**
     * 刪除
     */
    public function destroy(LegalPage $legalPage): RedirectResponse
    {
        $legalPage->delete();

        flash_success('法律頁面已刪除');

        return redirect(admin_list_url('admin.legal-pages.index'));
    }

    /**
     * AJAX 拖曳排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:legal_pages,id',
        ]);

        foreach ($request->ids as $index => $id) {
            LegalPage::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
