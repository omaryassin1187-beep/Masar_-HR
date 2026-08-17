<?php

namespace App\Services;

use App\Events\ImmediateResignationSubmitted;
use App\Events\ResignationSubmitted;
use App\Exceptions\ResignationException;
use App\Mail\ResignationSettlementMail;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Resignation;
use App\Models\ResignationSettlement;
use App\Models\Task;
use App\Models\User;
use App\Services\ResignationSettlementCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResignationService
{
    public function __construct(
        private ResignationSettlementCalculator $settlementCalculator
    ) {}


    public function submit(User $employee, array $data): Resignation
    {
        if (Resignation::activeForUser($employee->id)->exists()) {
            throw ResignationException::activeResignationExists();
        }

        $contract = $this->resolveCurrentContract($employee);

        if (! $contract) {
            throw ResignationException::noActiveContract();
        }

        $lastWorkingDay = $data['type'] === Resignation::TYPE_IMMEDIATE
            ? now()->toDateString()
            : now()->addDays($contract->termination_notice_days)->toDateString();

        $cancelledTasks = new Collection();

        $resignation = DB::transaction(function () use ($employee, $data, $contract, $lastWorkingDay, &$cancelledTasks) {
            $resignation = Resignation::create([
                'user_id'          => $employee->id,
                'type'             => $data['type'],
                'reason'           => $data['reason'] ?? null,
                'last_working_day' => $lastWorkingDay,
                'contract_id'      => $contract->id,
                'status'           => Resignation::STATUS_SUBMITTED,
            ]);
            foreach ($data['documents'] ?? [] as $file) {
                $storedPath = $file->storeAs(
                    'resignations/' . $resignation->id,
                    now()->timestamp . '_' . $file->getClientOriginalName(),
                    'private'
                );

                $resignation->documents()->create([
                    'type'      => Document::TYPE_RESIGNATION_SUPPORT,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                ]);
            }

            if ($data['type'] === Resignation::TYPE_IMMEDIATE) {
                $cancelledTasks = Task::where('assigned_to', $employee->id)
                    ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
                    ->get();

                if ($cancelledTasks->isNotEmpty()) {
                    Task::whereIn('id', $cancelledTasks->pluck('id'))
                        ->update(['status' => Task::STATUS_CANCELLED]);
                }
            }

            return $resignation;
        });

        event(new ResignationSubmitted($resignation));

        if ($resignation->isImmediate()) {
            $resignation->load('employee');
            event(new ImmediateResignationSubmitted($resignation, $cancelledTasks));
        }

        return $resignation;
    }

    public function listForHr(array $filters = []): LengthAwarePaginator
    {
        $query = Resignation::query()->with(['employee', 'classifiedBy'])->openForHr();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate(15);
    }

    public function classify(User $hrUser, Resignation $resignation, array $data): Resignation
    {
        if (! $resignation->isImmediate()) {
            throw ResignationException::notImmediate();
        }

        if (! $resignation->requiresClassification()) {
            throw ResignationException::alreadyClassified();
        }

        $resignation = DB::transaction(function () use ($hrUser, $resignation, $data) {
            $resignation->update([
                'hr_classification'       => $data['hr_classification'],
                'hr_classification_notes' => $data['hr_classification_notes'] ?? null,
                'classified_by'           => $hrUser->id,
                'classified_at'           => now(),
                'notice_period_treatment' => Resignation::resolveNoticePeriodTreatment($data['hr_classification']),
            ]);

            $this->finalize($resignation->fresh());

            return $resignation->fresh(['settlement', 'employee']);
        });

        $this->dispatchSettlementEmail($resignation);

        return $resignation;
    }

    public function finalizeDueNoticePeriods(): int
    {
        $due = Resignation::withNotice()
            ->where('status', Resignation::STATUS_SUBMITTED)
            ->whereDate('last_working_day', '<=', now()->toDateString())
            ->get();

        foreach ($due as $resignation) {
            $resignation = DB::transaction(function () use ($resignation) {
                $this->finalize($resignation);
                return $resignation->fresh(['settlement', 'employee']);
            });

            $this->dispatchSettlementEmail($resignation);
        }

        return $due->count();
    }

    private function finalize(Resignation $resignation): ResignationSettlement
    {
        $unusedLeave = $this->settlementCalculator->calculateUnusedLeave($resignation);
        $noticePeriodAmount = $this->settlementCalculator->calculateNoticePeriodAmount($resignation);
        $gratuity = $this->settlementCalculator->calculateEndOfServiceGratuity($resignation);

        $totalLeaveAmount = $unusedLeave['annual_leave_amount'] + $unusedLeave['sick_leave_amount'];

        $totalAmount = $this->settlementCalculator->calculateTotal(
            $resignation,
            $totalLeaveAmount,
            $noticePeriodAmount,
            $gratuity,
        );

        $settlement = ResignationSettlement::updateOrCreate(
            ['resignation_id' => $resignation->id],
            [
                'annual_leave_days'         => $unusedLeave['annual_leave_days'],
                'annual_leave_amount'       => $unusedLeave['annual_leave_amount'],
                'sick_leave_days'           => $unusedLeave['sick_leave_days'],
                'sick_leave_amount'         => $unusedLeave['sick_leave_amount'],
                'notice_period_amount'      => $noticePeriodAmount,
                'end_of_service_gratuity'   => $gratuity,
                'total_compensation_amount' => $totalAmount,
            ]
        );

        $resignation->contract?->update(['status' => 'terminated']);
        $resignation->employee?->update(['status' => 'inactive']);

        $resignation->update([
            'status'                 => Resignation::STATUS_CONTRACT_TERMINATED,
            'contract_terminated_at' => now(),
        ]);

        return $settlement;
    }

    private function dispatchSettlementEmail(Resignation $resignation): void
    {
        if (! $resignation->settlement || ! $resignation->employee?->email) {
            return;
        }

        try {
            Mail::to($resignation->employee->email)
                ->queue(new ResignationSettlementMail($resignation, $resignation->settlement));

            $resignation->settlement->update(['emailed_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Failed to queue resignation settlement email', [
                'resignation_id' => $resignation->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    private function resolveCurrentContract(User $employee): ?Contract
    {
        return Contract::where('user_id', $employee->id)
            ->whereIn('status', ['active', 'probation'])
            ->latest('start_date')
            ->first();
    }
}
