<?php

namespace App\Console\Commands;

use App\Models\PerformanceEvaluation;
use App\Models\User;
use App\Notifications\Evaluation\EvaluationReminderNotification;
use App\Services\Evaluation\EvaluationService;
use App\Services\Task\Concerns\NotifiesSafely;
use Illuminate\Console\Command;

class GenerateQuarterlyEvaluations extends Command
{
    use NotifiesSafely;

    protected $signature = 'evaluations:generate';
    protected $description = 'Generate evaluations for the previous quarter in one batch for all eligible employees, and notify managers';

    public function handle(EvaluationService $service): int
    {
        // The "just ended" quarter = the quarter containing yesterday (because the command runs on the first day of the new quarter)
        [$quarter, $year, $start, $end] = PerformanceEvaluation::quarterBoundsContaining(now()->subDay());

        $created = $service->generateForQuarter($quarter, $year, $start, $end);

        $managerIds = PerformanceEvaluation::pendingManager()
            ->forQuarter($quarter, $year)
            ->distinct()
            ->pluck('manager_id');

        foreach ($managerIds as $managerId) {
            $manager = User::find($managerId);
            $pendingCount = PerformanceEvaluation::pendingManager()
                ->forQuarter($quarter, $year)
                ->where('manager_id', $managerId)
                ->count();

            if ($manager) {
                $this->notifySafely($manager, new EvaluationReminderNotification($quarter, $year, $pendingCount));
            }
        }

        $this->info("Created {$created} evaluations for Q{$quarter}/{$year}.");

        return self::SUCCESS;
    }
}
