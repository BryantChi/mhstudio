<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MH Studio] 新報價請求：' . $this->quoteRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-request-notification',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'adminUrl' => route('admin.quote-requests.show', $this->quoteRequest),
            ],
        );
    }
}
