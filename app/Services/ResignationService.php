<?php
namespace App\Services;

use App\Events\ImmediateResignationSubmitted;
use App\Exceptions\ResignationException;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Resignation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResignationService
{
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

        $resignation = DB::transaction(function () use ($employee, $data, $contract, $lastWorkingDay) {
            $resignation = Resignation::create([
                'user_id'          => $employee->id,
                'type'             => $data['type'],
                'reason'           => $data['reason'] ?? null,
                'last_working_day' => $lastWorkingDay,
                'contract_id'      => $contract->id,
                'status'           => Resignation::STATUS_SUBMITTED,
            ]);

            foreach ($data['documents'] ?? [] as $file) {
                $storedPath = $file->store('resignations/' . $resignation->id, 'private');

                $resignation->documents()->create([
                    'type'          => Document::TYPE_RESIGNATION_SUPPORT,
                    'file_name'     => $file->hashName(),
                    'file_path'     => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }

            return $resignation;
        });

        if ($resignation->isImmediate()) {
            event(new ImmediateResignationSubmitted($resignation));
        }

        return $resignation;
    }

    public function getOpenTasksForResignation(Resignation $resignation): Collection
    {
        return Task::where('assigned_to', $resignation->user_id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();
    }

    public function reassignTasks(User $manager, Resignation $resignation, array $taskIds): void
    {
        $employee = User::where('id', $resignation->user_id)
            ->where('dep_id', $manager->dep_id)
            ->first();

        if (! $employee) {
            throw ResignationException::unauthorizedReassignment();
        }

        DB::transaction(function () use ($resignation, $taskIds) {
            $affected = Task::where('assigned_to', $resignation->user_id)
                ->whereIn('id', $taskIds)
                ->whereIn('status', ['pending', 'in_progress'])
                ->update(['status' => 'cancelled']);

            if ($affected !== count($taskIds)) {
                throw ResignationException::invalidTaskReassignment();
            }
        });
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

        return DB::transaction(function () use ($hrUser, $resignation, $data) {
            $resignation->update([
                'hr_classification'       => $data['hr_classification'],
                'hr_classification_notes' => $data['hr_classification_notes'] ?? null,
                'classified_by'           => $hrUser->id,
                'classified_at'           => now(),
                'notice_period_treatment' => Resignation::resolveNoticePeriodTreatment($data['hr_classification']),
            ]);

            $this->terminateContract($resignation);

            return $resignation->fresh();
        });
    }

    public function finalizeDueNoticePeriods(): int
    {
        $due = Resignation::withNotice()
            ->where('status', Resignation::STATUS_SUBMITTED)
            ->whereDate('last_working_day', '<=', now()->toDateString())
            ->get();

        foreach ($due as $resignation) {
            DB::transaction(fn () => $this->terminateContract($resignation));
        }

        return $due->count();
    }

    private function resolveCurrentContract(User $employee): ?Contract
    {
        return Contract::where('user_id', $employee->id)
            ->whereIn('status', ['active', 'probation'])
            ->latest('start_date')
            ->first();
    }

    private function terminateContract(Resignation $resignation): void
    {
        $resignation->contract?->update(['status' => 'terminated']);

        $resignation->update([
            'status'                 => Resignation::STATUS_CONTRACT_TERMINATED,
            'contract_terminated_at' => now(),
        ]);
    }
}
