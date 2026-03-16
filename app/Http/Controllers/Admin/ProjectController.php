<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectImage;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // _sortable 模式：回傳 JSON 給拖曳排序面板
        if ($request->has('_sortable')) {
            $items = Project::orderByDesc('is_featured')
                ->orderBy('order')
                ->orderByDesc('created_at')
                ->get(['id', 'title', 'order', 'is_featured']);

            return response()->json($items);
        }

        $query = Project::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('client', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $projects = $query->orderByDesc('is_featured')->orderByDesc('created_at')->orderBy('order')->paginate(15);
        $categories = Project::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.projects.index', compact('projects', 'categories'));
    }

    /**
     * 拖曳排序
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:projects,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Project::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function create(): View
    {
        $categories = Project::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.projects.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:projects',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string',
            'url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'tech_stack' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'order' => 'integer',
            'completed_at' => 'nullable|date',
        ]);

        // 將逗號分隔的 tech_stack 轉為陣列
        if (!empty($validated['tech_stack'])) {
            $validated['tech_stack'] = array_map('trim', explode(',', $validated['tech_stack']));
        }

        $validated['order'] = $validated['order'] ?? Project::max('order') + 1;

        Project::create($validated);

        flash_success('作品建立成功');

        return redirect()->route('admin.projects.index');
    }

    public function show(Project $project): View
    {
        $project->load([
            'images' => fn ($q) => $q->orderBy('order'),
            'milestones' => fn ($q) => $q->orderBy('order'),
            'files' => fn ($q) => $q->with('uploader')->latest(),
            'comments' => fn ($q) => $q->with('user')->latest(),
            'clients',
        ]);

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $project->load(['images' => fn ($q) => $q->orderBy('order')]);
        $categories = Project::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.projects.edit', compact('project', 'categories'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:projects,slug,' . $project->id,
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string',
            'url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'tech_stack' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'order' => 'integer',
            'completed_at' => 'nullable|date',
        ]);

        if (!empty($validated['tech_stack'])) {
            $validated['tech_stack'] = array_map('trim', explode(',', $validated['tech_stack']));
        }

        $project->update($validated);

        flash_success('作品更新成功');

        return redirect()->route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();
        flash_success('作品已刪除');

        return redirect()->route('admin.projects.index');
    }

    /* ===== 圖片庫管理 ===== */

    /**
     * 新增圖片到圖片庫 (AJAX)
     */
    public function galleryStore(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'image_url' => 'required|string|max:500',
            'media_item_id' => 'nullable|integer|exists:media_items,id',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
        ]);

        $maxOrder = $project->images()->max('order') ?? -1;

        $image = $project->images()->create([
            'image_url' => $validated['image_url'],
            'media_item_id' => $validated['media_item_id'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        // 同步更新 cover_image（以排序第一張為準）
        $this->syncCoverImage($project);

        return response()->json([
            'success' => true,
            'image' => $image,
        ]);
    }

    /**
     * 刪除圖片庫中的圖片 (AJAX)
     */
    public function galleryDestroy(ProjectImage $projectImage): JsonResponse
    {
        $project = $projectImage->project;
        $projectImage->delete();

        // 同步更新 cover_image
        $this->syncCoverImage($project);

        return response()->json(['success' => true]);
    }

    /**
     * 圖片庫排序 (AJAX)
     */
    public function galleryReorder(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:project_images,id',
        ]);

        foreach ($validated['ids'] as $index => $id) {
            ProjectImage::where('id', $id)
                ->where('project_id', $project->id)
                ->update(['order' => $index]);
        }

        // 同步更新 cover_image
        $this->syncCoverImage($project);

        return response()->json(['success' => true]);
    }

    /**
     * 更新圖片 alt_text / caption (AJAX)
     */
    public function galleryUpdateMeta(Request $request, ProjectImage $projectImage): JsonResponse
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
        ]);

        $projectImage->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * 同步 cover_image 欄位：以圖片庫排序第一張為準
     */
    private function syncCoverImage(Project $project): void
    {
        $firstImage = $project->images()->orderBy('order')->first();
        $project->updateQuietly([
            'cover_image' => $firstImage?->image_url,
        ]);
    }

    /* ===== 客戶專案管理 ===== */

    /**
     * 顯示專案客戶管理頁面
     */
    public function clients(Project $project): View
    {
        $project->load('clients');
        $users = User::orderBy('name')->get();

        return view('admin.projects.clients', compact('project', 'users'));
    }

    /**
     * 更新專案客戶關聯
     */
    public function updateClients(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'clients' => 'nullable|array',
            'clients.*.user_id' => 'required|exists:users,id',
            'clients.*.role' => 'required|in:owner,viewer',
        ]);

        // 同步客戶
        $syncData = [];
        foreach ($validated['clients'] ?? [] as $client) {
            $syncData[$client['user_id']] = ['role' => $client['role']];
        }

        $project->clients()->sync($syncData);

        flash_success('客戶權限已更新');

        return redirect()->route('admin.projects.clients', $project);
    }

    /**
     * 新增里程碑
     */
    public function addMilestone(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
            'order' => 'integer',
        ]);

        $validated['order'] = $validated['order'] ?? $project->milestones()->max('order') + 1;

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $project->milestones()->create($validated);

        flash_success('里程碑已新增');

        return redirect()->route('admin.projects.show', $project);
    }

    /**
     * 更新里程碑
     */
    public function updateMilestone(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
            'order' => 'sometimes|integer',
        ]);

        // 如果狀態變更為已完成，記錄完成時間
        if (isset($validated['status']) && $validated['status'] === 'completed' && $milestone->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif (isset($validated['status']) && $validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $milestone->update($validated);

        flash_success('里程碑已更新');

        return redirect()->route('admin.projects.show', $milestone->project);
    }

    /**
     * 刪除里程碑
     */
    public function destroyMilestone(ProjectMilestone $milestone): RedirectResponse
    {
        $project = $milestone->project;
        $milestone->delete();

        flash_success('里程碑已刪除');

        return redirect()->route('admin.projects.show', $project);
    }

    /**
     * 上傳檔案
     */
    public function uploadFile(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB
            'description' => 'nullable|string|max:500',
        ]);

        $uploadedFile = $request->file('file');
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('project-files/' . $project->id, $filename, 'public');

        $project->files()->create([
            'uploaded_by' => auth()->id(),
            'filename' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'description' => $request->description,
        ]);

        flash_success('檔案已上傳');

        return redirect()->route('admin.projects.show', $project);
    }

    /**
     * 刪除檔案
     */
    public function destroyFile(ProjectFile $file): RedirectResponse
    {
        $project = $file->project;

        // 刪除實體檔案
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        flash_success('檔案已刪除');

        return redirect()->route('admin.projects.show', $project);
    }

    /**
     * 新增管理員留言
     */
    public function addComment(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'is_internal' => 'boolean',
        ]);

        $project->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        flash_success('留言已新增');

        return redirect()->route('admin.projects.show', $project);
    }
}
