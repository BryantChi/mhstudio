<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Article::with(['author', 'category', 'tags']);

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 狀態篩選
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 分類篩選
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $articles = $query->latest()->paginate(15);
        $categories = Category::active()->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::active()->get();
        $tags = Tag::orderByUsage()->get();

        return view('admin.articles.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $article = Article::create($validated);

        // 同步標籤
        if (!empty($validated['tags'])) {
            $article->tags()->sync($validated['tags']);

            // 更新標籤使用次數
            foreach ($validated['tags'] as $tagId) {
                Tag::find($tagId)?->incrementCount();
            }
        }

        // 生成 SEO Meta
        $article->generateSeoMeta();

        flash_success('文章建立成功');

        return redirect()->route('admin.articles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article): View
    {
        $article->load(['author', 'category', 'tags', 'seoMeta']);
        return view('admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article): View
    {
        $categories = Category::active()->get();
        $tags = Tag::orderByUsage()->get();
        $article->load(['category', 'tags', 'seoMeta']);

        return view('admin.articles.edit', compact('article', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,' . $article->id,
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $article->update($validated);

        // 同步標籤
        $oldTags = $article->tags->pluck('id')->toArray();
        $newTags = $validated['tags'] ?? [];

        $article->tags()->sync($newTags);

        // 更新標籤使用次數
        foreach (array_diff($oldTags, $newTags) as $tagId) {
            Tag::find($tagId)?->decrementCount();
        }
        foreach (array_diff($newTags, $oldTags) as $tagId) {
            Tag::find($tagId)?->incrementCount();
        }

        flash_success('文章更新成功');

        return redirect()->route('admin.articles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        // 減少標籤使用次數
        foreach ($article->tags as $tag) {
            $tag->decrementCount();
        }

        $article->delete();

        flash_success('文章刪除成功');

        return redirect()->route('admin.articles.index');
    }
}
