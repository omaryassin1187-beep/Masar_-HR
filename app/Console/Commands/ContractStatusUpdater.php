<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractRenewal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ContractStatusUpdater extends Command
{
    protected $signature   = 'contracts:update-statuses';
    protected $description = 'Automatically updates contract statuses (probation → active, active → expired)';

    public function handle(): void
    {
        $today = Carbon::today();
        $activatedCount = Contract::where('status', Contract::STATUS_PROBATION)
            ->where('end_date', '>=', $today)  // العقد لم ينتهِ
            ->whereRaw(

                'DATE_ADD(start_date, INTERVAL probation_period_days DAY) <= ?',
                [$today]
            )
            ->update(['status' => Contract::STATUS_ACTIVE]);


        $expiredCount = Contract::whereIn('status', [
            Contract::STATUS_ACTIVE,
            Contract::STATUS_PROBATION,
        ])
            ->whereDate('end_date', '<', $today)
            ->update(['status' => Contract::STATUS_EXPIRED]);
        $renewalExpiredCount = ContractRenewal::where('status', ContractRenewal::STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->update(['status' => ContractRenewal::STATUS_EXPIRED]);
        $this->info("Activated: {$activatedCount} contracts, Expired: {$expiredCount} contracts, Expired renewals: {$renewalExpiredCount}.");


        }
}
