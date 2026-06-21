<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\Candidate;
use App\Models\User;
use App\Notifications\OfferExpiredNotification;
use Illuminate\Console\Command;

class ExpireOffers extends Command
{
    protected $signature = 'offers:expire';
    protected $description = 'Expire pending offers older than 4 days';

    public function handle()
    {
        $expiredOffers = Offer::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(4))
            ->get();

        foreach ($expiredOffers as $offer) {
            $offer->update(['status' => 'rejected']);
            $offer->candidate->update(['status' => Candidate::STATUS_REJECTED]);

            $hrUsers = User::role('HR')->get();
            foreach ($hrUsers as $hr) {
                $hr->notify(new OfferExpiredNotification($offer));
            }
        }

        $this->info(count($expiredOffers) . ' offers expired.');
    }
}
