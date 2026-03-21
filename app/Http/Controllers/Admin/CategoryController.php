<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use ReordersItems;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        // 排序模式：回傳精簡 JSON 資料（僅頂層分類）
        if ($request->has('_sortable')) {
            $items = Category::whereNull('parent_id')->ordered()->get(['id', 'name', 'order']);

            return response()->json($items);
        }

        $query = Category::query()
            ->withCount(['articles', 'children'])
            ->with('parent');

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 狀態篩選
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->ordered()->paginate(15);

        // 獲取樹狀結構（只在沒有搜尋條件時顯示，限制2層深度）
        $tree = collect();
        if (!$request->filled('search') && !$request->filled('status')) {
            $tree = Category::query()
                ->whereNull('parent_id')
                ->withCount('articles')
                ->with(['children' => function ($query) {
                    $query->orderBy('order')
                          ->withCount('articles')
                          ->with(['children' => function ($q) {
                              $q->orderBy('order')
                                ->withCount('articles');
                          }]);
                }])
                ->orderBy('order')
                ->get();
        }

        return view('admin.categories.index', compact('categories', 'tree'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $parentCategories = Category::topLevel()->active()->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer',
        ]);

        // 表單送出 1-based，轉為 0-based 儲存
        $validated['order'] = isset($validated['order']) ? max(0, $validated['order'] - 1) : (Category::max('order') ?? -1) + 1;

        $category = Category::create($validated);
        $this->syncOrder(Category::class, $category->id, $category->order, ['parent_id' => $category->parent_id]);

        flash_success('分類建立成功');

        return redirect(admin_list_url('admin.categories.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $category->load(['parent', 'children', 'articles']);
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $parentCategories = Category::topLevel()
            ->where('id', '!=', $category->id)
            ->active()
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer',
        ]);

        // 防止父分類設定為自己或自己的子分類
        if (!empty($validated['parent_id'])) {
            if ($validated['parent_id'] == $category->id) {
                flash_error('父分類不可設定為自己');
                return redirect()->back()->withInput();
            }

            $descendants = collect($category->getDescendants())->pluck('id');
            if ($descendants->contains($validated['parent_id'])) {
                flash_error('父分類不可設定為自己的子分類');
                return redirect()->back()->withInput();
            }
        }

        // 表單送出 1-based，轉為 0-based 儲存
        if (isset($validated['order'])) {
            $validated['order'] = max(0, $validated['order'] - 1);
        }

        $category->update($validated);
        $this->syncOrder(Category::class, $category->id, $category->order, ['parent_id' => $category->parent_id]);

        flash_success('分類更新成功');

        return redirect(admin_list_url('admin.categories.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // 檢查是否有子分類
        if ($category->hasChildren()) {
            flash_error('無法刪除有子分類的分類');
            return redirect()->back();
        }

        // 檢查是否有文章
        if ($category->articles()->exists()) {
            flash_error('無法刪除有文章的分類');
            return redirect()->back();
        }

        $category->delete();

        flash_success('分類刪除成功');

        return redirect(admin_list_url('admin.categories.index'));
    }

    /**
     * AJAX 拖曳排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Category::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
