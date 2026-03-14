<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /**
     * 電子報列表
     */
    public function index(Request $request): View
    {
        $query = Newsletter::with('creator')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $newsletters = $query->paginate(15);

        return view('admin.newsletters.index', compact('newsletters'));
    }

    /**
     * 建立電子報表單
     */
    public function create(): View
    {
        $subscriberCount = Subscriber::active()->count();

        return view('admin.newsletters.create', compact('subscriberCount'));
    }

    /**
     * 儲存電子報
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $newsletter = Newsletter::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        flash_success('電子報已建立');

        return redirect()->route('admin.newsletters.edit', $newsletter);
    }

    /**
     * 電子報詳情（發送報告）
     */
    public function show(Newsletter $newsletter): View
    {
        $newsletter->load(['logs.subscriber', 'creator']);

        $stats = [
            'total' => $newsletter->total_recipients,
            'sent' => $newsletter->sent_count,
            'failed' => $newsletter->failed_count,
            'opened' => $newsletter->logs()->where('status', 'opened')->count(),
        ];

        $logs = $newsletter->logs()->with('subscriber')->latest()->paginate(20);

        return view('admin.newsletters.show', compact('newsletter', 'stats', 'logs'));
    }

    /**
     * 編輯電子報表單（僅草稿）
     */
    public function edit(Newsletter $newsletter): View
    {
        abort_if($newsletter->status !== 'draft', 403, '只能編輯草稿狀態的電子報');

        $subscriberCount = Subscriber::active()->count();

        return view('admin.newsletters.edit', compact('newsletter', 'subscriberCount'));
    }

    /**
     * 更新電子報
     */
    public function update(Request $request, Newsletter $newsletter): RedirectResponse
    {
        abort_if($newsletter->status !== 'draft', 403);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $newsletter->update($validated);

        flash_success('電子報已更新');

        return redirect()->back();
    }

    /**
     * 刪除電子報（僅草稿）
     */
    public function destroy(Newsletter $newsletter): RedirectResponse
    {
        abort_if($newsletter->status !== 'draft', 403);

        $newsletter->logs()->delete();
        $newsletter->delete();

        flash_success('電子報已刪除');

        return redirect()->route('admin.newsletters.index');
    }

    /**
     * 發送電子報
     */
    public function send(Newsletter $newsletter): RedirectResponse
    {
        abort_if($newsletter->status !== 'draft', 403, '只能發送草稿狀態的電子報');

        SendNewsletterJob::dispatch($newsletter);

        flash_success('電子報已開始發送');

        return redirect()->route('admin.newsletters.index');
    }

    /**
     * 預覽電子報
     */
    public function preview(Newsletter $newsletter)
    {
        $subscriber = new Subscriber(['email' => 'preview@example.com', 'name' => '預覽用戶']);

        return new NewsletterMail($newsletter, $subscriber);
    }

    /**
     * 發送測試郵件
     */
    public function sendTest(Request $request, Newsletter $newsletter): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $subscriber = new Subscriber(['email' => $request->email, 'name' => '測試收件人']);
        Mail::to($request->email)->send(new NewsletterMail($newsletter, $subscriber));

        flash_success('測試郵件已發送至 ' . $request->email);

        return redirect()->back();
    }
}
