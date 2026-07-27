<?php

namespace App\Services;

use App\Models\{Contract, ContractRenewal, Setting, User};
use App\Notifications\contracts\ContractRenewalAcceptedNotification;
use App\Notifications\contracts\ContractRenewalOfferNotification;
use App\Notifications\contracts\ContractRenewalRejectedNotification;
use Illuminate\Support\Facades\{DB, Hash};
use Carbon\Carbon;

class ContractRenewalService
{

    public function createRenewal(Contract $contract, array $data): ContractRenewal
    {
        return DB::transaction(function () use ($contract, $data) {
            $existing = $contract->renewals()
                ->where('status', ContractRenewal::STATUS_PENDING)
                ->first();

            if ($existing) {
                abort(422, 'A pending renewal request already exists for this contract.');
            }

            $settings = Setting::instance();

            $renewal = ContractRenewal::create([
                'contract_id'                => $contract->id,
                'user_id'                    => $contract->user_id,
                'created_by'                 => auth()->id(),
                'new_start_date'             => $data['new_start_date'],
                'new_end_date'               => $data['new_end_date'],
                'new_hour_price'             => $data['new_hour_price'],
                'new_weekend_days'           => $data['new_weekend_days'] ?? $settings->weekend_days,
                'new_working_hours_per_day'  => $data['new_working_hours_per_day'] ?? $settings->workingHoursPerDay(),
                'status'                     => ContractRenewal::STATUS_PENDING,
                'expires_at'                 => Carbon::now()->addDays(7),
            ]);

            $contract->user->notify(
                new ContractRenewalOfferNotification($renewal)
            );

            return $renewal;
        });
    }


    public function respond(ContractRenewal $renewal, string $action): void
    {
        if (! $renewal->isPending()) {
            abort(422, 'This request has already been responded to.');
        }

        if ($renewal->isExpired()) {
            $renewal->update(['status' => ContractRenewal::STATUS_EXPIRED]);
            abort(403, 'The renewal link has expired.');
        }

        DB::transaction(function () use ($renewal, $action) {
            if ($action === 'accept') {
                $this->handleAcceptance($renewal);
            } else {
                $this->handleRejection($renewal);
            }
        });
    }

    private function handleAcceptance(ContractRenewal $renewal): void
    {
        $renewal->update([
            'status'               => ContractRenewal::STATUS_ACCEPTED,
            'employee_response_at' => now(),
        ]);

        $settings = Setting::instance();
        $oldContract = $renewal->contract;

        $newContract = Contract::create([
            'user_id'                 => $renewal->user_id,
            'offer_id'                => $oldContract->offer_id,
            'hour_price'              => $renewal->new_hour_price,
            'working_hours_per_day'   => $renewal->new_working_hours_per_day,
            'weekend_days'            => $renewal->new_weekend_days,
            'start_date'              => $renewal->new_start_date,
            'end_date'                => $renewal->new_end_date,
            'probation_period_days'   => 0,
            'termination_notice_days' => $settings->termination_notice_days,
            'jurisdiction'            => $settings->jurisdiction,
            'signed_at'               => today(),
            'status'                  => Contract::STATUS_ACTIVE,

            'candidate_signature_path' => $oldContract->candidate_signature_path,
            'candidate_signed_at'      => $oldContract->candidate_signed_at,
            'hr_signature_path'        => $oldContract->hr_signature_path,
            'hr_signed_at'             => $oldContract->hr_signed_at,
        ]);

        $newContract->user->employeeSalaries()->create([
            'hour_price'     => $newContract->hour_price,
            'currency'       => $settings->currency,
            'effective_from' => $newContract->start_date,
            'effective_to'   => $newContract->end_date,
        ]);

        User::role('HR')->each(function ($hr) use ($renewal) {
            $hr->notify(new ContractRenewalAcceptedNotification($renewal));
        });
    }


    private function handleRejection(ContractRenewal $renewal): void
    {
        $renewal->update([
            'status'               => ContractRenewal::STATUS_REJECTED,
            'employee_response_at' => now(),
        ]);

        User::role('HR')->each(function ($hr) use ($renewal) {
            $hr->notify(new ContractRenewalRejectedNotification($renewal));
        });
    }
}
