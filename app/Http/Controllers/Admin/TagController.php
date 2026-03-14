<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Tag::query();

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // 排序
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tags = $query->paginate(15);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags',
            'slug' => 'nullable|string|max:50|unique:tags',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        Tag::create($validated);

        flash_success('標籤建立成功');

        return redirect()->route('admin.tags.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): View
    {
        $tag->load('articles');
        return view('admin.tags.show', compact('tag'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'slug' => 'nullable|string|max:50|unique:tags,slug,' . $tag->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $tag->update($validated);

        flash_success('標籤更新成功');

        return redirect()->route('admin.tags.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        // 檢查是否有文章使用此標籤
        if ($tag->articles()->exists()) {
            flash_warning('此標籤仍被文章使用,已解除關聯');
            $tag->articles()->detach();
        }

        $tag->delete();

        flash_success('標籤刪除成功');

        return redirect()->route('admin.tags.index');
    }

    /**
     * 同步標籤使用次數
     */
    public function syncCount(Tag $tag): RedirectResponse
    {
        $tag->syncCount();

        flash_success('標籤使用次數已同步');

        return redirect()->back();
    }

    /**
     * 批次同步所有標籤使用次數
     */
    public function syncAllCounts(): RedirectResponse
    {
        $tags = Tag::all();

        foreach ($tags as $tag) {
            $tag->syncCount();
        }

        flash_success('所有標籤使用次數已同步');

        return redirect()->back();
    }
}
