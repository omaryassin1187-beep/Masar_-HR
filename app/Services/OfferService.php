<?php

namespace App\Services;

use App\Mail\JobOfferMail;
use App\Models\Candidate;
use App\Models\JobPosting;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\OfferAcceptedNotification;
use App\Notifications\OfferRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class OfferService
{
    public function send(JobPosting $jobPosting, array $data): Offer
    {
        return DB::transaction(function () use ($jobPosting, $data) {

            $offer = Offer::create([
                'candidate_id'         => $data['candidate_id'],
                'job_posting_id'       => $jobPosting->id,
                'hour_price'           => $data['hour_price'],
                'start_date'           => $data['start_date'],
                'weekend_days'         => $data['weekend_days'],
                'working_hour_per_day' => $data['working_hour_per_day'],
                'status'               => 'pending',
            ]);

            $offer->load(['candidate', 'jobPosting.requisition']);

            $acceptUrl = URL::temporarySignedRoute(
                'emails.respond',
                now()->addDays(4),
                ['offer' => $offer->id, 'action' => 'accept']
            );

            $rejectUrl = URL::temporarySignedRoute(
                'emails.respond',
                now()->addDays(4),
                ['offer' => $offer->id, 'action' => 'reject']
            );

            Mail::to($offer->candidate->email)
                ->send(new JobOfferMail($offer, $acceptUrl, $rejectUrl));

            return $offer;
        });
    }

    public function respond(Offer $offer, string $action): string
    {
        if (! $offer->isPending()) {
            return match ($offer->status) {
                'accepted' => 'You have already accepted this offer.',
                'rejected' => 'You have already declined this offer.',
                default    => 'This offer is no longer available.',
            };
        }

        DB::transaction(function () use ($offer, $action) {
            if ($action === 'accept') {
                $this->handleAcceptance($offer);
            } else {
                $this->handleRejection($offer);
            }
        });

        return $action === 'accept'
            ? 'Congratulations! You have accepted the offer. Our HR team will contact you soon.'
            : 'Thank you for your response. Your declination has been recorded.';
    }


    private function handleAcceptance(Offer $offer): void
    {
        $offer->update(['status' => 'accepted']);

        $offer->candidate->update(['status' => Candidate::STATUS_HIRED]);

        $offer->jobPosting->update(['status' => 'closed']);

        $hrUsers = User::role('HR')->get();
    Notification::send($hrUsers, new OfferacceptedNotification($offer));
    }

    private function handleRejection(Offer $offer): void
    {
        $offer->update(['status' => 'rejected']);

        $offer->candidate->update(['status' => Candidate::STATUS_REJECTED]);

        $hrUsers = User::role('HR')->get();
    Notification::send($hrUsers, new OfferrejectedNotification($offer));
    }
}
