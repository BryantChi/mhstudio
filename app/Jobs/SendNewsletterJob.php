<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterLog;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Newsletter $newsletter) {}

    /**
     * 任務最大執行時間（秒）
     */
    public int $timeout = 3600;

    /**
     * 最大重試次數
     */
    public int $tries = 1;

    public function handle(): void
    {
        $newsletter = $this->newsletter;
        $newsletter->update(['status' => 'sending']);

        // 使用 count 查詢而非載入全部記錄
        $totalRecipients = Subscriber::active()->count();
        $newsletter->update(['total_recipients' => $totalRecipients]);

        // 使用 chunk 分批處理，避免記憶體爆炸
        Subscriber::active()->chunk(200, function ($subscribers) use ($newsletter) {
            foreach ($subscribers as $subscriber) {
                try {
                    Mail::to($subscriber->email)->send(new NewsletterMail($newsletter, $subscriber));

                    NewsletterLog::create([
                        'newsletter_id' => $newsletter->id,
                        'subscriber_id' => $subscriber->id,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    $newsletter->increment('sent_count');
                } catch (\Exception $e) {
                    NewsletterLog::create([
                        'newsletter_id' => $newsletter->id,
                        'subscriber_id' => $subscriber->id,
                        'status' => 'failed',
                        'error_message' => substr($e->getMessage(), 0, 500),
                    ]);

                    $newsletter->increment('failed_count');
                }
            }
        });

        $newsletter->refresh();
        $newsletter->update([
            'status' => $newsletter->failed_count > 0 && $newsletter->sent_count === 0 ? 'failed' : 'sent',
            'sent_at' => now(),
        ]);
    }
}
