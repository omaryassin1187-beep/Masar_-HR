<?php
// app/Listeners/SendSignatureRequestToCandidate.php
namespace App\Listeners;

use App\Events\OfferAccepted;
use App\Notifications\contracts\SignContractRequestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class SendSignatureRequestToCandidate
{
    public function handle(OfferAccepted $event): void
    {
        $offer = $event->offer;

        $signedUrl = URL::temporarySignedRoute(
            'contracts.candidate.sign',
            now()->addDays(7),
            ['offer' => $offer->id]
        );

        Notification::route('mail', $offer->candidate->email)
            ->notify(new SignContractRequestNotification($offer, $signedUrl));
    }
}
