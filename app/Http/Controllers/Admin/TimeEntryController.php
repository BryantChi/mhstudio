<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeEntryController extends Controller
{
    /**
     * 工時列表
     */
    public function index(Request $request): View
    {
        $query = TimeEntry::with(['user', 'project', 'task']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->where('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('started_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('billable')) {
            $query->where('is_billable', $request->billable === '1');
        }

        $timeEntries = $query->latest('started_at')->paginate(20)->withQueryString();

        // 本週統計
        $weekMinutes = TimeEntry::thisWeek()->byUser(auth()->id())->sum('duration_minutes');
        $monthMinutes = TimeEntry::thisMonth()->byUser(auth()->id())->sum('duration_minutes');

        // 檢查是否有進行中的計時器
        $runningEntry = TimeEntry::running()->byUser(auth()->id())->with(['project', 'task'])->first();

        $projects = Project::orderBy('title')->get();
        $users = User::orderBy('name')->get();

        return view('admin.time-entries.index', compact(
            'timeEntries',
            'weekMinutes',
            'monthMinutes',
            'runningEntry',
            'projects',
            'users'
        ));
    }

    /**
     * 儲存新工時紀錄（手動輸入）
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'is_billable' => 'nullable|boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_billable'] = $request->has('is_billable');

        TimeEntry::create($validated);
        flash_success('工時紀錄已新增');

        return redirect(admin_list_url('admin.time-entries.index'));
    }

    /**
     * 更新工時紀錄
     */
    public function update(Request $request, TimeEntry $timeEntry): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'description' => 'nullable|string|max:255',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'is_billable' => 'nullable|boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $validated['is_billable'] = $request->has('is_billable');
        $validated['duration_minutes'] = null; // 觸發自動計算

        $timeEntry->update($validated);
        flash_success('工時紀錄已更新');

        return redirect(admin_list_url('admin.time-entries.index'));
    }

    /**
     * 刪除工時紀錄
     */
    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $timeEntry->delete();
        flash_success('工時紀錄已刪除');

        return redirect(admin_list_url('admin.time-entries.index'));
    }

    /**
     * 啟動計時器
     */
    public function startTimer(Request $request): JsonResponse
    {
        // 先停止已有的計時器
        $running = TimeEntry::running()->byUser(auth()->id())->first();
        if ($running) {
            $running->stop();
        }

        $entry = TimeEntry::create([
            'user_id' => auth()->id(),
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
            'description' => $request->description,
            'started_at' => now(),
            'is_billable' => $request->get('is_billable', true),
            'hourly_rate' => $request->hourly_rate,
        ]);

        return response()->json([
            'success' => true,
            'entry' => $entry->load(['project', 'task']),
            'message' => '計時器已啟動',
        ]);
    }

    /**
     * 停止計時器
     */
    public function stopTimer(TimeEntry $timeEntry): JsonResponse
    {
        $timeEntry->stop();

        return response()->json([
            'success' => true,
            'entry' => $timeEntry->fresh()->load(['project', 'task']),
            'message' => '計時器已停止',
        ]);
    }

    /**
     * 取得專案任務列表（API）
     */
    public function projectTasks(Project $project): JsonResponse
    {
        $tasks = $project->tasks()
            ->where('status', '!=', 'completed')
            ->orderBy('title')
            ->get(['id', 'title', 'status']);

        return response()->json($tasks);
    }

    /**
     * 工時報表
     */
    public function report(Request $request): View
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'project');

        $query = TimeEntry::with(['user', 'project', 'task'])
            ->whereNotNull('duration_minutes')
            ->where('started_at', '>=', $dateFrom)
            ->where('started_at', '<=', $dateTo . ' 23:59:59');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $entries = $query->get();

        // 分組統計
        if ($groupBy === 'project') {
            $grouped = $entries->groupBy('project_id')->map(function ($group) {
                return [
                    'name' => $group->first()->project?->title ?? '未指定專案',
                    'total_minutes' => $group->sum('duration_minutes'),
                    'billable_minutes' => $group->where('is_billable', true)->sum('duration_minutes'),
                    'billable_amount' => $group->where('is_billable', true)->sum(fn ($e) => $e->billable_amount),
                    'entries_count' => $group->count(),
                ];
            })->sortByDesc('total_minutes');
        } else {
            $grouped = $entries->groupBy('user_id')->map(function ($group) {
                return [
                    'name' => $group->first()->user?->name ?? '未知',
                    'total_minutes' => $group->sum('duration_minutes'),
                    'billable_minutes' => $group->where('is_billable', true)->sum('duration_minutes'),
                    'billable_amount' => $group->where('is_billable', true)->sum(fn ($e) => $e->billable_amount),
                    'entries_count' => $group->count(),
                ];
            })->sortByDesc('total_minutes');
        }

        $totalMinutes = $entries->sum('duration_minutes');
        $billableMinutes = $entries->where('is_billable', true)->sum('duration_minutes');
        $totalAmount = $entries->where('is_billable', true)->sum(fn ($e) => $e->billable_amount);

        $users = User::orderBy('name')->get();
        $projects = Project::orderBy('title')->get();

        return view('admin.time-entries.report', compact(
            'grouped',
            'dateFrom',
            'dateTo',
            'groupBy',
            'totalMinutes',
            'billableMinutes',
            'totalAmount',
            'users',
            'projects'
        ));
    }
}
