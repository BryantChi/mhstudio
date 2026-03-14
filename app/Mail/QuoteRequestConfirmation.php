<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '您的報價請求已收到 — ' . $this->quoteRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-request-confirmation',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'statusUrl' => route('quote-request.status', ['token' => $this->quoteRequest->token]),
            ],
        );
    }
}
