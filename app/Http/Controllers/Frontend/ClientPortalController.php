<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 客戶專區儀表板 - 列出客戶的專案
     */
    public function dashboard(): View
    {
        $projects = auth()->user()->clientProjects()
            ->with(['milestones', 'files'])
            ->latest()
            ->get();

        return view('frontend.client.dashboard', compact('projects'));
    }

    /**
     * 專案詳情
     */
    public function projectShow(Project $project): View
    {
        // 授權：用戶必須已連結到此專案
        abort_unless(
            auth()->user()->clientProjects()->where('project_id', $project->id)->exists(),
            403
        );

        $project->load([
            'milestones' => fn ($q) => $q->orderBy('order'),
            'files' => fn ($q) => $q->latest(),
            'comments' => fn ($q) => $q->visible()->with('user')->latest(),
        ]);

        return view('frontend.client.project-show', compact('project'));
    }

    /**
     * 新增留言
     */
    public function addComment(Request $request, Project $project): RedirectResponse
    {
        abort_unless(
            auth()->user()->clientProjects()->where('project_id', $project->id)->exists(),
            403
        );

        $request->validate(['content' => 'required|string|max:2000']);

        $project->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_internal' => false,
        ]);

        return back()->with('success', '留言已送出');
    }

    /**
     * 下載檔案
     */
    public function downloadFile(Project $project, ProjectFile $file): StreamedResponse
    {
        abort_unless(
            auth()->user()->clientProjects()->where('project_id', $project->id)->exists(),
            403
        );

        abort_unless($file->project_id === $project->id, 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
