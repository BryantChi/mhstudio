<?php

namespace App\Mail;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractToClient extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.setting('company_name', 'MH Studio').'] 合約文件：'.$this->contract->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-to-client',
            with: ['contract' => $this->contract],
        );
    }

    public function attachments(): array
    {
        $contract = $this->contract->load(['client', 'project', 'creator', 'items']);
        $pdf = Pdf::loadView('admin.contracts.pdf', compact('contract'))->setPaper('A4', 'portrait');

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->contract->contract_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
