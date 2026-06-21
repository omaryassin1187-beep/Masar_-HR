<?php
// app/Services/Recruitment/EmployeeOnboardingService.php

namespace App\Services;

use App\Models\{Offer, User, Contract, Candidate, Setting};
use App\Models\Attendance_Leaves\LeaveBalance;
use App\Notifications\WelcomeEmployeeNotification;
use App\Notifications\OfferAcceptedNotification;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class EmployeeOnboardingService
{
    //هذه الخدمة مسؤولة عن إتمام عملية التوظيف بعد قبول العرض الوظيفي، بما في ذلك إنشاء حساب الموظف، إنشاء العقد، وتحديث حالة المرشح
    public function createFromOffer(Offer $offer): User
    {
        return DB::transaction(function () use ($offer) {
            $candidate = $offer->candidate;
            $settings  = Setting::instance();
            $user = User::create([
                'full_name'             => $candidate->full_name,
                'email'            => $candidate->email,
                'dep_id'    => $offer->jobPosting->requisition->department_id,
                'status'           => 'inactive',
                'is_first_login'   => true,
            ]);
            $user->assignRole('employee');
            $startDate = Carbon::parse($offer->start_date);
            Contract::create([
                'user_id'                  => $user->id,
                'offer_id'                 => $offer->id,
                'hour_price'               => $offer->hour_price,
                'working_hours_per_day' => $offer->working_hour_per_day,
                'weekend_days'             => $offer->weekend_days,
                'start_date'               => $startDate,
                'end_date'                 => $startDate->copy()->addYear(),
                'probation_period_days'    => Contract::PROBATION_DAYS,
                'termination_notice_days'  => $settings->termination_notice_days,
                'jurisdiction'             => $settings->jurisdiction,
                'status'                   => Contract::STATUS_PROBATION,
            ]);

            $user->leaveBalance()->create(['leave_type' => 'annual', 'used_days' => 0, 'total_days' => $settings->annual_leave_days]);
            $user->leaveBalance()->create(['leave_type' => 'sick', 'used_days' => 0, 'total_days' => $settings->sick_leave_days]);
            $user->leaveBalance()->create(['leave_type' => 'unpaid', 'used_days' => 0, 'total_days' => null]);

            $candidate->update(['status' => Candidate::STATUS_HIRED]);

            $user->notify(new WelcomeEmployeeNotification($user->email));

            $hrUsers = User::role('HR')->get();
            foreach ($hrUsers as $hr) {
                $hr->notify(new OfferAcceptedNotification($offer));
            }
            return $user;
        });
    }

    public function completeOnboarding(User $user): void
    {
        //تحديث حالة الموظف إلى نشط، وتحديث تاريخ إتمام عملية الانضمام في العقد
        DB::transaction(function () use ($user) {
            $user->update([
                'onboarding_completed_at' => now(),
            ]);

            $user->contract()->update([
                'signed_at' => today(),
            ]);
        });
    }
}
