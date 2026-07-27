<?php

namespace App\Services;

use App\Models\Salary\OverTime;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\Salary\OverTimePendingApprovalNotification;
use App\Notifications\Salary\VoluntaryOverTimeApprovedForHRNotification;
use App\Notifications\Salary\VoluntaryOverTimeApprovedNotification;
use App\Notifications\Salary\VoluntaryOverTimeRejectedNotification;
use App\Notifications\Salary\VoluntaryOverTimeSubmittedNotification;
use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Attendance_Leaves\Attendance;
use Carbon\Carbon;

class OverTimeService
{


    private function ensureEmployeeBelongsToManager(int $employeeId): void
    {
        $manager = auth()->user();

        $employee = User::findOrFail($employeeId);

        if ($employee->dep_id !== $manager->dep_id) {
            throw new HttpException(
                403,
                'You can only create,approve and reject overtime requests for employees in your department.'
            );
        }
    }

    private function notifyHrAboutOverTime(OverTime $overTime): void
    {

        $hrUsers = User::role('HR')->get();

        foreach ($hrUsers as $hr) {

            $hr->notify(new OverTimePendingApprovalNotification($overTime));
        }
    }

    public function storeByManager(array $data): OverTime
    {
        $this->ensureEmployeeBelongsToManager($data['user_id']);

        $exists = OverTime::where('user_id', $data['user_id'])
            ->whereDate('date', $data['date'])
            ->whereIn('status', [
                'pending_hr_approval',
                'approved',
            ])
            ->exists();

        if ($exists) {
            throw new \Exception('An overtime request already exists for this employee on the selected date.');
        }

        $employee = User::findOrFail($data['user_id']);

        $salary = $employee->employeeSalaries()
            ->whereDate('effective_from', '<=', $data['date'])
            ->where(function ($query) use ($data) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $data['date']);
            })
            ->first();

        if (!$salary) {
            throw new HttpException(
                422,
                'The employee does not have an active salary for the selected date.'
            );
        }

        $overtime = OverTime::create([
            'user_id' => $data['user_id'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],

            'hour_price' => $salary->hour_price,

            'type' => 'mandatory',
            'status' => 'pending_hr_approval',

            'requested_by' => auth()->id(),

            'notes' => $data['notes'] ?? null,
        ]);

        $this->notifyHrAboutOverTime($overtime);

        return $overtime;
    }

    public function getManagerOverTimeRequests()
    {
        return OverTime::query()
            ->with([
                'user',
                'requestedBy',
                'approvedBy',
            ])
            ->where('type', 'mandatory')
            ->latest()
            ->get();
    }

        public function delete(int $id): void
    {
        $user = auth()->user();

        $overTime = OverTime::query()
            ->where('id', $id)
            ->where('requested_by', $user->id)
            ->firstOrFail();

        if ($overTime->status !== 'pending_manager_approval'
            && $overTime->status !== 'pending_hr_approval') {
            throw new Exception(
                'This overtime request cannot be deleted because it has already been processed.'
            );
        }

        // الموظف لا يحذف إلا الطلبات التطوعية
        if ($user->hasRole('employee') && $overTime->type !== 'voluntary') {
            throw new Exception('You are not allowed to delete this overtime request.');
        }

        // المدير لا يحذف إلا الطلبات الإلزامية التي أنشأها
        if ($user->hasRole('manager') && $overTime->type !== 'mandatory') {
            throw new Exception('You are not allowed to delete this overtime request.');
        }

        $overTime->delete();
    }

    public function approveEmployeeRequest(int $id): OverTime
    {
        $overTime = OverTime::query()
            ->where('id', $id)
            ->where('type', 'voluntary')
            ->firstOrFail();

        if ($overTime->status !== 'pending_manager_approval') {
            throw ValidationException::withMessages([
                'status' => 'You can only approve overtime requests that are pending manager approval.',
            ]);
        }

        $this->ensureEmployeeBelongsToManager($overTime->user_id);

        $overTime->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // إشعار للموظف
        $overTime->user->notify(
            new VoluntaryOverTimeApprovedNotification($overTime)
        );


        $hrUsers = User::role('HR')->get();

        foreach ($hrUsers as $hr) {

            $hr->notify(new VoluntaryOverTimeApprovedForHRNotification($overTime));
        }

        return $overTime->fresh([
            'user',
            'requestedBy',
            'approvedBy',
        ]);
    }

    public function rejectEmployeeRequest(int $id): OverTime
    {
        $overTime = OverTime::query()
            ->where('id', $id)
            ->where('type', 'voluntary')
            ->firstOrFail();

        if ($overTime->status !== 'pending_manager_approval') {
            throw ValidationException::withMessages([
                'status' => 'You can only reject overtime requests that are pending manager approval.',
            ]);
        }

        $this->ensureEmployeeBelongsToManager($overTime->user_id);

        $overTime->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        // إشعار للموظف
        $overTime->user->notify(
            new VoluntaryOverTimeRejectedNotification($overTime)
        );

        return $overTime->fresh([
            'user',
            'requestedBy',
            'approvedBy',
        ]);
    }



    private function notifyManagerAboutVoluntaryOverTime(OverTime $overTime): void
    {
        $manager = User::role('manager')
            ->where('dep_id', $overTime->user->dep_id)
            ->first();

        if ($manager) {
            $manager->notify(
                new VoluntaryOverTimeSubmittedNotification($overTime)
            );
        }
    }

    public function storeByEmployee(array $data): OverTime
    {
        $user = auth()->user();

        $status = 'approved';

        if ($user->hasRole('employee')) {
            $status = 'pending_manager_approval';
        }

        $exists = OverTime::where('user_id', $user->id)
            ->whereDate('date', $data['date'])
            ->whereIn('status', [
                'pending_manager_approval',
                'approved',
            ])
            ->exists();

        if ($exists) {
            throw new Exception(
                'You already have an overtime request for the selected date.'
            );
        }

        $salary = $user->employeeSalaries()
            ->whereDate('effective_from', '<=', $data['date'])
            ->where(function ($q) use ($data) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $data['date']);
            })
            ->latest('effective_from')
            ->first();

        if (!$salary) {
            throw new Exception('No active salary found for this employee.');
        }

        $overtime = OverTime::create([
            'user_id'      => $user->id,
            'date'         => $data['date'],
            'start_time'   => $data['start_time'],
            'end_time'     => $data['end_time'],
            'hour_price'   => $salary->hour_price,
            'type'         => 'voluntary',
            'status'       => $status,
            'requested_by' => $user->id,
            'notes'        => $data['notes'] ?? null,
        ]);

        if ($user->hasRole('employee')) {
            $this->notifyManagerAboutVoluntaryOverTime($overtime);
        }

        if ($user->hasRole('manager')) {
            $hrUsers = User::role('HR')->get();

            foreach ($hrUsers as $hr) {

                $hr->notify(new OverTimePendingApprovalNotification($overtime));
            }
        }

        return $overtime;
    }

    public function completeApprovedOverTimes(): void
    {
        $overTimes = OverTime::query()
            ->where('status', 'approved')
            ->whereDate('date', '<=', today())
            ->get();

        foreach ($overTimes as $overTime) {

            $attendance = Attendance::query()
                ->where('user_id', $overTime->user_id)
                ->whereDate('date', $overTime->date)
                ->with('latestSession')
                ->first();

            if (!$attendance || !$attendance->latestSession?->check_out) {
                continue;
            }

            $amount = $this->calculateOverTimeAmount(
                $overTime,
                $attendance->latestSession->check_out
            );

            $overTime->update([
                'status' => 'completed',
                'amount' => $amount,
            ]);
        }
    }

    private function calculateOverTimeAmount(
        OverTime $overTime,
        string $checkOut
    ): float {

        $plannedStart = Carbon::parse(
            $overTime->date . ' ' . $overTime->start_time
        );

        $plannedEnd = Carbon::parse(
            $overTime->date . ' ' . $overTime->end_time
        );

        $actualCheckOut = Carbon::parse($checkOut);

        $actualEnd = $actualCheckOut->min($plannedEnd);

        $workedMinutes = max(
            0,
            $plannedStart->diffInMinutes($actualEnd, false)
        );

        return round(
            ($workedMinutes / 60)
                * $overTime->hour_price
                * $overTime->multiplier,
            2
        );
    }
}
