<?php

namespace App\Services;

use App\Events\TerminationApproved;
use App\Models\Setting;
use App\Models\Termination\TerminationRequest;
use App\Models\User;
use App\Notifications\Termination\TerminationRequestCreatedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Termination\TerminationApprovals;
use App\Notifications\Termination\DeletedTerminationRequestNotification;

class TerminationService
{
    protected function sendTerminationNotifications(
        TerminationRequest $terminationRequest
    ): void {
        /*
     * HR created the termination request.
     * Notify the employee's manager.
     */
        if ($terminationRequest->created_by_role === 'HR') {

            $manager = $terminationRequest->user->manager;

            if ($manager) {
                $manager->notify(
                    new TerminationRequestCreatedNotification($terminationRequest)
                );
            }
        }

        /*
     * Manager created the termination request.
     * Notify HR.
     */
        if ($terminationRequest->created_by_role === 'manager') {

            $hr = User::role('HR')->first();

            if ($hr) {
                $hr->notify(
                    new TerminationRequestCreatedNotification($terminationRequest)
                );
            }
        }
    }

    private function createImmediateTerminationDetail(
        TerminationRequest $terminationRequest,
        array $data
    ): void {
        $documentsPath = null;

        if (isset($data['documents'])) {
            $documentsPath = $data['documents']->store(
                'termination-documents',
                'public'
            );
        }

        $terminationRequest->immediateTerminationDetail()->create([
            'subtype' => $data['subtype'],
            'compensation_amount' => $data['compensation_amount'] ?? null,
            'legal_reason' => $data['legal_reason'] ?? null,
            'documents_path' => $documentsPath,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function store(array $data): TerminationRequest
    {
        return DB::transaction(function () use ($data) {

            $creator = auth()->user();

            /*
         * Only HR and Manager can create
         * a termination request.
         */
            if (!$creator->hasAnyRole(['HR', 'manager'])) {
                throw ValidationException::withMessages([
                    'user' => 'You are not allowed to create a termination request.',
                ]);
            }

            /*
         * Get creator role from Spatie.
         */
            $creatorRole = $creator->getRoleNames()->first();

            /*
         * Get employee.
         */
            $user = User::findOrFail($data['user_id']);

            /*
         * Manager can only create termination requests
         * for employees in his department.
         */
            if (
                $creatorRole === 'manager' &&
                $creator->department_id !== $user->department_id
            ) {
                throw ValidationException::withMessages([
                    'user_id' => 'You can only create termination requests for employees in your department.',
                ]);
            }

            /*
         * Check if employee already has
         * a termination request.
         */
            if ($user->terminationRequest()->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'This employee already has a termination request.',
                ]);
            }

            /*
         * Get active contract.
         */
            $terminationDate = Carbon::parse($data['termination_date']);

            $contract = $user->contracts()
                ->whereDate('start_date', '<=', $terminationDate)
                ->where(function ($query) use ($terminationDate) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $terminationDate);
                })
                ->latest('start_date')
                ->first();

            if (!$contract) {
                throw ValidationException::withMessages([
                    'user_id' => 'The employee does not have an active contract.',
                ]);
            }

            /*
         * Check probation period.
         */
            $probationEndDate = Carbon::parse($contract->start_date)
                ->addDays($contract->probation_period_days);

            $isInProbation = $terminationDate->lte($probationEndDate);

            /*
         * Calculate last working day.
         */
            if ($isInProbation || $data['type'] === 'immediate') {

                $lastWorkingDay = $terminationDate->copy();

                $noticePeriodDays = 0;
            } else {

                $noticePeriodDays = $contract->termination_notice_days;

                $lastWorkingDay = $terminationDate
                    ->copy()
                    ->addDays($noticePeriodDays);
            }

            /*
         * Create termination request.
         */
            $terminationRequest = TerminationRequest::create([
                'user_id' => $user->id,
                'contract_id' => $contract->id,

                'created_by' => $creator->id,
                'created_by_role' => $creatorRole,

                'type' => $data['type'],

                'termination_date' => $terminationDate->toDateString(),
                'last_working_day' => $lastWorkingDay->toDateString(),

                'notice_period_days' => $noticePeriodDays,

                'ready_for_admin' => false,
                'status' => 'pending',
            ]);

            if ($data['type'] === 'immediate') {
                $this->createImmediateTerminationDetail(
                    $terminationRequest,
                    $data
                );
            }

            $firstApprovalRole = $creatorRole === 'HR'
                ? 'manager'
                : 'HR';

            $terminationRequest->approvals()->createMany([
                [
                    'role' => $firstApprovalRole,
                    'step' => 1,
                    'status' => 'pending',
                ],
                [
                    'role' => 'admin',
                    'step' => 2,
                    'status' => 'pending',
                ],
            ]);

            /*
         * Send notification to the next approver.
         */
            $this->sendTerminationNotifications($terminationRequest);

            return $terminationRequest->load([
                'user',
                'createdBy',
                'approvals',
                'immediateTerminationDetail',
            ]);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {

            $terminationRequest = TerminationRequest::with([
                'approvals',
            ])->findOrFail($id);

            $firstApproval = $terminationRequest->approvals
                ->where('step', 1)
                ->first();

            if (!$firstApproval || $firstApproval->status !== 'pending') {
                throw ValidationException::withMessages([
                    'termination' => 'This termination request cannot be deleted because the first approval has already been processed.',
                ]);
            }

            if ($terminationRequest->created_by !== auth()->id()) {
                throw ValidationException::withMessages([
                    'termination' => 'You can only delete a termination request created by you.',
                ]);
            }

            $this->sendTerminationDeletedNotification($terminationRequest);
            $terminationRequest->delete();
        });
    }

    protected function sendTerminationDeletedNotification(TerminationRequest $terminationRequest): void
    {

        if ($terminationRequest->created_by_role === 'HR') {

            $manager = $terminationRequest->user->manager;

            if ($manager) {
                $manager->notify(
                    new DeletedTerminationRequestNotification($terminationRequest)
                );
            }
        }

        if ($terminationRequest->created_by_role === 'manager') {

            User::role('HR')->get()->each(function ($hr) use ($terminationRequest) {
                $hr->notify(
                    new DeletedTerminationRequestNotification($terminationRequest)
                );
            });
        }
    }

    public function approve(
        TerminationRequest $terminationRequest,
        User $approver,
        ?string $reason = null
    ) {
        return DB::transaction(function () use (
            $terminationRequest,
            $approver,
            $reason
        ) {

            // 1. تحديد دور المستخدم
            if ($approver->hasRole('manager')) {
                $role = 'manager';
            } elseif ($approver->hasRole('HR')) {
                $role = 'HR';
            } elseif ($approver->hasRole('admin')) {
                $role = 'admin';
            } else {
                throw ValidationException::withMessages([
                    'approver' => 'You are not authorized to approve termination requests.',
                ]);
            }

            // 2. تجليب خطوة الموافقة الحالية المنتظرة
            $approval = $terminationRequest->approvals()
                ->where('status', 'pending')
                ->orderBy('step')
                ->first();

            if (!$approval) {
                throw ValidationException::withMessages([
                    'approval' => 'This termination request has no pending approvals.',
                ]);
            }

            // 3. التحقق من دور خطوة الموافقة
            if ($approval->role !== $role) {
                throw ValidationException::withMessages([
                    'approval' => 'It is not your turn to approve this termination request.',
                ]);
            }

            // 4. تحديث سجل الموافقة
            $approval->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'decision_reason' => $reason,
            ]);

            // 5. البحث عن خطوات موافقة متبقية
            $nextApproval = $terminationRequest->approvals()
                ->where('status', 'pending')
                ->orderBy('step')
                ->first();

            if ($nextApproval) {

                if ($nextApproval->role === 'admin') {



                    $this->notifyAdmin(

                        $terminationRequest->fresh()

                    );
                }

                return $terminationRequest->fresh([
                    'approvals',

                ]);
            }

            $terminationRequest->update([
                'status' => 'approved',
            ]);

            $eventClass = \App\Events\TerminationApproved::class; // تأكد من المسار الصحيح للـ Event


            event(new $eventClass($terminationRequest->fresh()));


            return $terminationRequest->fresh(['approvals']);
        });
    }

    public function reject(
        TerminationRequest $terminationRequest,
        User $approver,
        ?string $reason = null
    ) {
        return DB::transaction(function () use (
            $terminationRequest,
            $approver,
            $reason
        ) {

            // 1. تحديد دور المستخدم
            if ($approver->hasRole('manager')) {
                $role = 'manager';
            } elseif ($approver->hasRole('HR')) {
                $role = 'HR';
            } elseif ($approver->hasRole('admin')) {
                $role = 'admin';
            } else {
                throw ValidationException::withMessages([
                    'approver' => 'You are not authorized to reject termination requests.',
                ]);
            }

            $approval = $terminationRequest->approvals()
                ->where('status', 'pending')
                ->orderBy('step')
                ->first();

            if (!$approval) {
                throw ValidationException::withMessages([
                    'approval' => 'This termination request has no pending approvals.',
                ]);
            }

            if ($approval->role !== $role) {
                throw ValidationException::withMessages([
                    'approval' => 'It is not your turn to reject this termination request.',
                ]);
            }

            $approval->update([
                'status' => 'rejected',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'decision_reason' => $reason,
            ]);

            /*
         * 5. إذا كان الرافض Admin
         *    فهذا هو الرفض النهائي.
         */
            if ($role === 'admin') {

                $terminationRequest->update([
                    'status' => 'rejected',
                ]);

                return $terminationRequest->fresh([
                    'approvals',
                ]);
            }

            /*
         * 6. إذا كان الرافض HR أو Manager
         *    لا نرفض الطلب بالكامل.
         *    ننتقل للخطوة التالية.
         */
            $nextApproval = $terminationRequest->approvals()
                ->where('status', 'pending')
                ->orderBy('step')
                ->first();

            if ($nextApproval) {

                if ($nextApproval->role === 'admin') {
                    $this->notifyAdmin(
                        $terminationRequest->fresh()
                    );
                }

                return $terminationRequest->fresh([
                    'approvals',
                ]);
            }

            /*
         * هذا احتياط فقط:
         * المفروض أن الـ Admin موجود دائمًا كآخر خطوة.
         */
            throw ValidationException::withMessages([
                'approval' => 'No final admin approval step exists for this termination request.',
            ]);
        });
    }

    protected function notifyAdmin(
        TerminationRequest $terminationRequest
    ): void {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(
                new TerminationRequestCreatedNotification($terminationRequest)
            );
        }
    }


    public function calculateUnusedLeaveCompensation(User $employee): array
    {
        /*
     * Get the current effective salary.
     */
        $salary = $employee->employeeSalaries()
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();

        if (!$salary) {
            throw new \Exception(
                "No active salary found for employee ID: {$employee->id}"
            );
        }

        /*
     * Get settings.
     */
        $settings = Setting::first();

        if (!$settings) {
            throw new \Exception('System settings not found.');
        }

        /*
     * Calculate daily working hours.
     */
        $checkIn = Carbon::parse($settings->expected_check_in);
        $checkOut = Carbon::parse($settings->expected_check_out);

        $workingHoursPerDay = $checkIn->diffInMinutes($checkOut) / 60;

        /*
     * Get annual and sick leave balances.
     */
        $leaveBalances = $employee->leaveBalance()
            ->whereIn('leave_type', ['annual', 'sick'])
            ->get()
            ->keyBy('leave_type');

        $annualBalance = $leaveBalances->get('annual');
        $sickBalance = $leaveBalances->get('sick');

        /*
     * Calculate remaining days.
     */
        $annualRemainingDays = $annualBalance
            ? max(
                0,
                $annualBalance->total_days - $annualBalance->used_days
            )
            : 0;

        $sickRemainingDays = $sickBalance
            ? max(
                0,
                $sickBalance->total_days - $sickBalance->used_days
            )
            : 0;

        /*
     * Calculate daily rate.
     */
        $dailyRate = $salary->hour_price * $workingHoursPerDay;

        /*
     * Calculate compensation.
     */
        $annualCompensation =
            $annualRemainingDays * $dailyRate;

        $sickCompensation =
            $sickRemainingDays * $dailyRate;

        return [
            'annual_leave_days' => $annualRemainingDays,
            'annual_leave_amount' => $annualCompensation,

            'sick_leave_days' => $sickRemainingDays,
            'sick_leave_amount' => $sickCompensation,

            'total_days' =>
            $annualRemainingDays + $sickRemainingDays,

            'total_amount' =>
            $annualCompensation + $sickCompensation,
        ];
    }
}
