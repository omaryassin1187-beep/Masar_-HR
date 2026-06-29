<?php

namespace App\Listeners;

use App\Events\HrSignedContract;
use App\Models\Contract;
use App\Models\User;
use App\Notifications\ContractCompletedNotification;
use App\Notifications\WelcomeEmployeeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CompleteContractAfterHrSignature
{
    public function handle(HrSignedContract $event): void
    {
        $contract = $event->contract;

        if ($contract->hr_signed_at) {
            return;
        }

        DB::transaction(function () use ($event, $contract) {
            $contract->update([
                'hr_signature_path' => $event->hrSignaturePath,
                'hr_signed_at'      => now(),
                'signed_at'         => now(),
                'status'            => Contract::STATUS_PROBATION,
            ]);

            $contract->user->update([
                'status'                  => 'inactive',
                'onboarding_completed_at' => null,
            ]);
        });

        try {
            // ✅ إشعار الترحيب للموظف
            $contract->user->notify(new WelcomeEmployeeNotification($contract->user->email));

            // ✅ إشعار اكتمال العقد لـ HR
        } catch (\Throwable $e) {
            Log::error('Failed to send contract completion notifications', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
