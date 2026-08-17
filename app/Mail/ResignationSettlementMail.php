<?php

namespace App\Mail;

use App\Models\Resignation;
use App\Models\ResignationSettlement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResignationSettlementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        public Resignation $resignation,
        public ResignationSettlement $settlement,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'مخالصتك المالية النهائية — MasarHR');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resignation-settlement',
            with: [
                'resignation' => $this->resignation,
                'settlement'  => $this->settlement,
                'employee'    => $this->resignation->employee,
            ],
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Resignation settlement email delivery failed', [
            'resignation_id' => $this->resignation->id,
            'error'          => $exception->getMessage(),
        ]);
    }
}
