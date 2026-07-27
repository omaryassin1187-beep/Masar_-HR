<?php

namespace App\Notifications\Evaluation;

use App\Models\PerformanceEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryIncreaseRecommendationNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(public readonly PerformanceEvaluation $evaluation) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->evaluation->employee;

        return [
            'type' => 'salary_increase_recommendation',
            'evaluation_id' => $this->evaluation->id,
            'employee_id' => $this->evaluation->employee_id,
            'employee_name' => $employee->full_name,
            'evaluation_score' => $this->evaluation->final_score,
            'quarter' => $this->evaluation->quarter,
            'year' => $this->evaluation->year,
            'message' => "💰 Salary increase recommendation: {$employee->full_name} scored {$this->evaluation->final_score}% and qualifies for a raise.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $employee = $this->evaluation->employee;

        return new BroadcastMessage([
            'data' => [
                'type' => 'salary_increase_recommendation',
                'evaluation_id' => $this->evaluation->id,
                'employee_id' => $this->evaluation->employee_id,
                'employee_name' => $employee->full_name,
                'evaluation_score' => $this->evaluation->final_score,
                'quarter' => $this->evaluation->quarter,
                'year' => $this->evaluation->year,
                'message' => "💰 Salary increase recommendation: {$employee->full_name} scored {$this->evaluation->final_score}% and qualifies for a raise.",
            ]
        ]);
    }
}
