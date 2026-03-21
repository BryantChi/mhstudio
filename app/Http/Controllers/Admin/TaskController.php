<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Traits\ReordersItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    use ReordersItems;
    /**
     * 任務列表 / 看板視圖
     */
    public function index(Request $request): View
    {
        $query = Task::with(['project', 'assignee', 'creator']);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $view = $request->get('view', 'board');

        if ($view === 'board') {
            // 看板視圖 — 按狀態分組
            $todoTasks = (clone $query)->where('status', 'todo')->orderBy('order')->get();
            $inProgressTasks = (clone $query)->where('status', 'in_progress')->orderBy('order')->get();
            $inReviewTasks = (clone $query)->where('status', 'in_review')->orderBy('order')->get();
            $completedTasks = (clone $query)->where('status', 'completed')->orderBy('order')->latest('completed_at')->get();

            $tasks = null;
        } else {
            // 列表視圖
            $todoTasks = $inProgressTasks = $inReviewTasks = $completedTasks = null;

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $tasks = $query->latest()->paginate(15)->withQueryString();
        }

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();

        return view('admin.tasks.index', compact(
            'view',
            'tasks',
            'todoTasks',
            'inProgressTasks',
            'inReviewTasks',
            'completedTasks',
            'projects',
            'users'
        ));
    }

    /**
     * 新增任務表單
     */
    public function create(Request $request): View
    {
        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();
        $selectedProjectId = $request->get('project_id');

        return view('admin.tasks.create', compact('projects', 'users', 'selectedProjectId'));
    }

    /**
     * 儲存新任務
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,in_review,completed',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer',
        ]);

        // 表單送出 1-based，轉為 0-based 儲存
        $validated['order'] = isset($validated['order']) ? max(0, $validated['order'] - 1) : (Task::max('order') ?? -1) + 1;

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task = Task::create($validated);
        $this->syncOrder(Task::class, $task->id, $task->order);
        flash_success('任務建立成功');

        return redirect(admin_list_url('admin.tasks.index'));
    }

    /**
     * 編輯任務表單
     */
    public function edit(Task $task): View
    {
        $task->load('timeEntries');
        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();

        return view('admin.tasks.edit', compact('task', 'projects', 'users'));
    }

    /**
     * 更新任務
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:todo,in_progress,in_review,completed',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);
        $this->syncOrder(Task::class, $task->id, $task->order);
        flash_success('任務更新成功');

        return redirect(admin_list_url('admin.tasks.index'));
    }

    /**
     * 刪除任務
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();
        flash_success('任務已刪除');

        return redirect(admin_list_url('admin.tasks.index'));
    }

    /**
     * AJAX 更新任務狀態（看板拖曳）
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,in_review,completed',
            'order' => 'nullable|integer',
        ]);

        $data = ['status' => $request->status];

        if ($request->has('order')) {
            $data['order'] = $request->order;
        }

        if ($request->status === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif ($request->status !== 'completed') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => '任務狀態已更新',
        ]);
    }
}
