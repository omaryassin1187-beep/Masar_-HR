<?php

namespace App\Mail;

use App\Models\Termination\TerminationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TerminationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TerminationRequest $terminationRequest
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Employment Termination Notice - Masar HR',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.termination_approved',
            with: [
                'employee' => $this->terminationRequest->user,
                'terminationRequest' => $this->terminationRequest,
            ],
        );
    }
}