<?php

namespace App\Listeners;

// استيراد الحدث الصحيح بدقة
use App\Events\TerminationApproved;
use App\Mail\TerminationApprovedMail;
use App\Mail\TerminationCompensationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;
use App\Models\User;
use App\Services\TerminationService;
use Carbon\Carbon;

class ExecuteTermination
{
    public function __construct(
        protected TerminationService $terminationService
    ) {}


    public function handle(TerminationApproved $event): void
    {
        $terminationRequest = $event->terminationRequest;

        DB::transaction(function () use ($terminationRequest) {

            $employee = $terminationRequest->user;
            $lastWorkingDay = $terminationRequest->last_working_day;

            $leaveCompensation = null;
            $immediateCompensation = null;

            /*
         * Calculate immediate termination compensations
         * before changing contract, salary, or employee status.
         */
            if ($terminationRequest->type === 'immediate') {

                $leaveCompensation = $this->terminationService->calculateUnusedLeaveCompensation(
                    $employee
                );

                $immediateDetail = $terminationRequest
                    ->immediateTerminationDetail;

                if (
                    $immediateDetail &&
                    $immediateDetail->subtype === 'company_composition'
                ) {
                    $immediateCompensation = $immediateDetail->compensation_amount;
                }

                /*
             * Send compensation details to employee.
             */
                Mail::to($employee->email)
                    ->send(
                        new TerminationCompensationMail(
                            $terminationRequest,
                            $leaveCompensation,
                            $immediateCompensation
                        )
                    );
            }

            /*
         * Get active contract.
         */
            $contract = $employee->contracts()
                ->whereIn('status', ['active', 'probation'])
                ->latest('start_date')
                ->first();

            if (!$contract) {
                throw new \Exception(
                    "No active contract found for employee ID: {$employee->id}"
                );
            }

            /*
         * Update contract.
         */
            $contract->update([
                'end_date' => $lastWorkingDay,
                'status' => 'non_renewable',
            ]);

            /*
         * Update employee salary.
         */
            $employee->employeeSalaries()
                ->whereNull('effective_to')
                ->update([
                    'effective_to' => $lastWorkingDay,
                ]);

            /*
         * Immediate termination → employee becomes inactive.
         */
            if ($terminationRequest->type === 'immediate') {
                $employee->update([
                    'status' => 'inactive',
                ]);
            }

            /*
         * Standard termination email.
         */
            if ($terminationRequest->type === 'standard') {
                Mail::to($employee->email)
                    ->send(
                        new TerminationApprovedMail($terminationRequest)
                    );
            }
        });
    }
}
