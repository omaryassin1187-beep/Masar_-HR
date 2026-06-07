<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateInterviewScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public Interview $interview;

    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Invitation — '.$this->interview->jobPosting->requisition->job_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate_interview_scheduled',
            with: [
                'candidateName' => $this->interview->candidate->full_name,
                'jobTitle' => $this->interview->jobPosting->requisition->job_title,
                'scheduledAt' => $this->interview->scheduled_at->format('Y-m-d H:i'),
                'location' => $this->interview->location_type === 'online'
                    ? 'Online — '.$this->interview->location_details
                    : 'On-site — '.$this->interview->location_details,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
