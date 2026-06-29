<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\User;
use App\Notifications\contracts\CandidateDidNotSignContractNotification;
use Illuminate\Console\Command;

class ExpireUnsignedContracts extends Command
{
    protected $signature = 'contracts:expire-unsigned';
    protected $description = 'Expire unsigned contracts after 7 days';

    public function handle()
    {
        $expiredContracts = Contract::where('status', Contract::STATUS_AWAITING_HR_SIGNATURE)
            ->whereNull('candidate_signed_at')
            ->where('created_at', '<=', now()->subDays(7))
            ->get();

        foreach ($expiredContracts as $contract) {
            $contract->update(['status' => 'expired']);
            $contract->user->update(['status' => 'rejected']);

            // ✅ إشعار لـ HR
            $hrUsers = User::role('HR')->get();
            foreach ($hrUsers as $hr) {
                $hr->notify(new  CandidateDidNotSignContractNotification($contract));
            }
        }

        $this->info(count($expiredContracts) . ' unsigned contracts expired.');
    }
}
