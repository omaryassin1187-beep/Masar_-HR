<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Offer $offer,
        public readonly string $acceptUrl,
        public readonly string $rejectUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Job Offer — ' . $this->offer->jobPosting->requisition->title,
        );
    }

    public function content(): Content
    {
        $offer = $this->offer;

        return new Content(
            view: 'emails.offer',
            with: [
                'candidateName'        => $offer->candidate->full_name,
                'jobTitle'             => $offer->jobPosting->job_title,
                'startDate'            => $offer->start_date->format('Y-m-d'),
                'hourPrice'            => number_format($offer->hour_price, 2),
                'workingHoursPerDay'   => $offer->working_hour_per_day,
                'weekendDays'          => $offer->weekend_days,
                'estimatedMonthlySalary' => number_format($offer->estimatedMonthlySalary(), 2),
                'acceptUrl'            => $this->acceptUrl,
                'rejectUrl'            => $this->rejectUrl,
            ],
        );
    }
}
