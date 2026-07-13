<?php
// app/Listeners/ResendSignatureRequestToCandidate.php
namespace App\Listeners;

use App\Events\HrRequestedResignature;
use App\Models\Contract;
use App\Notifications\contracts\SignContractRequestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class ResendSignatureRequestToCandidate
{
    public function handle(HrRequestedResignature $event): void
    {
        $contract = $event->contract;

        $contract->update([
            'status'                   => Contract::STATUS_AWAITING_CANDIDATE_SIGNATURE,
            'candidate_signed_at'      => null,
            'candidate_signature_path' => null,
        ]);

        $offer = $contract->offer;

        $signedUrl = URL::temporarySignedRoute(
            'contracts.candidate.sign',
            now()->addDays(7),
            ['offer' => $offer->id]
        );

        Notification::route('mail', $offer->candidate->email)
            ->notify(new SignContractRequestNotification($offer, $signedUrl, isResend: true));
    }
}
