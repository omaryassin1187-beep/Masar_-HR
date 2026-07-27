<?php
// app/Listeners/CreateOrUpdateContractFromCandidateSignature.php
namespace App\Listeners;

use App\Events\CandidateSignedContract;
use App\Models\Contract;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\contracts\HrActionRequiredNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class CreateOrUpdateContractFromCandidateSignature
{
    public function handle(CandidateSignedContract $event): void
    {
        set_time_limit(300);
        $offer = $event->offer;

        // ✅ معالجة موحّدة لحالتين: "أول توقيع" و"إعادة توقيع بعد طلب HR"
        $contract = DB::transaction(function () use ($offer, $event) {
            $existing = $offer->contracts()->latest()->first();

            if ($existing) {
                if ($existing->candidate_signed_at) {
                    return $existing; // ✅ idempotency: تم توقيعه مسبقاً، تجاهل أي محاولة مكررة
                }

                $existing->update([
                    'candidate_signature_path' => $event->signaturePath,
                    'candidate_signed_at'      => now(),
                    'status'                   => Contract::STATUS_AWAITING_HR_SIGNATURE,
                ]);

                return $existing;
            }

            $candidate = $offer->candidate;

            $depId = $offer->jobPosting?->requisition?->department_id;

            $user = User::firstOrCreate(
                ['email' => $candidate->email],
                [
                    'full_name'      => $candidate->full_name,
                    'dep_id' => $depId,
                    'status'         => 'inactive',
                    'is_first_login' => true,
                    'password'       => null,
                ]
            );
            $user->assignRole('employee');


            $settings  = Setting::instance();
            $startDate = $offer->start_date;

            $contract =  Contract::create([
                'user_id'                  => $user->id,
                'offer_id'                 => $offer->id,
                'hour_price'               => $offer->hour_price,
                'working_hours_per_day'     => $offer->working_hours_per_day,
                'weekend_days'             => $offer->weekend_days,
                'start_date'               => $startDate,
                'end_date'                 => $startDate->copy()->addYear(),
                'probation_period_days'    => $settings->probation_period_days,
                'termination_notice_days'  => $settings->termination_notice_days,
                'jurisdiction'             => $settings->jurisdiction,
                'candidate_signature_path' => $event->signaturePath,
                'candidate_signed_at'      => now(),
                'status'                   => Contract::STATUS_AWAITING_HR_SIGNATURE,
            ]);

            $user->employeeSalaries()
                ->firstOrCreate(
                    ['effective_from' => $contract->start_date],
                    [
                        'hour_price' => $contract->hour_price,
                        'currency'   => $settings->currency,
                        'effective_to'   => $contract->end_date,
                    ]
                );

            return $contract;
        });

        // ✅ الإشعارات بعد التزام الـ transaction، وبمعزل عنها
        $hrUsers = User::role('HR')->get();

        foreach ($hrUsers as $hr) {
            try {
                // 1. توليد الروابط الموقعة رقمياً
                $signUrl = URL::temporarySignedRoute(
                    'contracts.hr.sign',
                    now()->addDays(7),
                    ['contract' => $contract->id, 'hr' => $hr->id]
                );



                $resignUrl = URL::temporarySignedRoute(
                    'contracts.hr.resign',
                    now()->addDays(7),
                    ['contract' => $contract->id, 'hr' => $hr->id]
                );

                // 🎯 [هنا الفحص الصارم] - طباعة الروابط مباشرة في ملف السجلات قبل إرسال الإشعار
                Log::info('--- MASAR_HR_DEBUG: Checking Generated URLs in Listener ---', [
                    'HR_ID'      => $hr->id,
                    'ContractID' => $contract->id,
                    'SIGN_URL'   => $signUrl,
                    'RESIGN_URL' => $resignUrl,
                ]);

                // 2. إرسال الإشعار
                $hr->notify(new HrActionRequiredNotification(
                    $contract,
                    $signUrl,
                    $resignUrl
                ));
            } catch (\Throwable $e) {
                Log::error('Failed to notify HR or generate signed URLs', [
                    'contract_id' => $contract->id,
                    'hr_id'       => $hr->id,
                    'error'       => $e->getMessage()
                ]);
            }
        }
    }
}
