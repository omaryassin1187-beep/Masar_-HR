<?php

namespace App\Services;

use App\Events\OfferAccepted;
use App\Mail\JobOfferMail;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\candidate\NoMoreCandidatesNotification;
use App\Notifications\offers\OfferRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class OfferService
{
    //الخدمة المسؤولة عن إرسال العروض الوظيفية والتعامل مع ردود المرشحين عليها
    public function send(JobPosting $jobPosting, array $data): Offer
    {
        return DB::transaction(function () use ($jobPosting, $data) {


            $offer = Offer::create([
                'candidate_id'         => $data['candidate_id'],
                'job_posting_id'       => $jobPosting->id,
                'hour_price'           => $data['hour_price'],
                'start_date'           => $data['start_date'],
                'weekend_days'         => $data['weekend_days'],
                'working_hours_per_day' => $data['working_hours_per_day'],
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
    //الخدمة المسؤولة عن التعامل مع ردود المرشحين على العروض الوظيفية سواء بالقبول أو الرفض
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
    //تحديث حالة العرض إلى مقبول، إغلاق الإعلان الوظيفي، وإطلاق حدث لإنشاء حساب الموظف الجديد
    {
        $offer->update(['status' => 'accepted']);
        OfferAccepted::dispatch($offer);
    }

    private function handleRejection(Offer $offer): void
    {
        $offer->update(['status' => 'rejected']);
        $offer->candidate->update(['status' => Candidate::STATUS_REJECTED]);

        $currentRank = Interview::where('candidate_id', $offer->candidate_id)
            ->where('job_posting_id', $offer->job_posting_id)
            ->value('rank');

        $nextInterview = Interview::where('job_posting_id', $offer->job_posting_id)
            ->where('rank', '>', $currentRank)
            ->whereHas('candidate', function ($q) {
                $q->where('status', Candidate::STATUS_INTERVIEWED)
                    ->whereDoesntHave('offers', fn($sub) => $sub->where('status', 'pending'));
            })
            ->orderBy('rank')
            ->first();

        $nextCandidate = $nextInterview?->candidate;

        // إشعار HR مع بيانات المرشح التالي
        $hrUsers = User::role('HR')->get();
        Notification::send($hrUsers, new OfferRejectedNotification($offer, $nextCandidate));

        if (! $nextInterview) {
    User::role('HR')->each(function ($hr) use ($offer) {
        $hr->notify(new NoMoreCandidatesNotification($offer->jobPosting));
    });
    return;
}
    }

    private function sendOfferEmail(Offer $offer): void
    {
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

        $offer->load(['candidate', 'jobPosting.requisition']);

        Mail::to($offer->candidate->email)
            ->send(new JobOfferMail($offer, $acceptUrl, $rejectUrl));
    }
}
