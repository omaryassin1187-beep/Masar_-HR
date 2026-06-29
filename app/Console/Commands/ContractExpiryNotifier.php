<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractRenewal;
use App\Models\User;
use App\Notifications\contracts\ContractsExpiringSoonNotification;
use Illuminate\Console\Command;

class ContractExpiryNotifier extends Command
{

    protected $signature = 'contracts:notify-expiring';

    protected $description = 'Notifies HR of contracts expiring within 30 days';

    public function handle(): void
    {

        $expiringContracts = Contract::with(['user', 'user.department'])
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->whereDoesntHave('renewals', function ($q) {
                $q->where('status', ContractRenewal::STATUS_PENDING);
            })
            ->orderBy('end_date')
            ->get();

        if ($expiringContracts->isEmpty()) {
            $this->info('No contracts require notification today.');
            return;
        }

        User::role('HR')->each(function ($hr) use ($expiringContracts) {
            $hr->notify(
                new ContractsExpiringSoonNotification($expiringContracts)
            );
        });

        $this->info("Notification sent for {$expiringContracts->count()} contracts.");
    }
}
