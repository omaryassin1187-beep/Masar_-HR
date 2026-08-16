<?php

namespace App\Mail;

use App\Models\Termination\TerminationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TerminationCompensationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TerminationRequest $terminationRequest,
        public array $leaveCompensation,
        public ?float $immediateCompensation = null,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Termination Compensation Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.termination-compensation',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}