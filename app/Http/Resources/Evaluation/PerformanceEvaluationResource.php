<?php

namespace App\Http\Resources\Evaluation;

use App\Models\PerformanceEvaluation;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceEvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'quarter' => $this->quarter,
            'year'    => $this->year,
            'period'  => [
                'start' => $this->period_start?->toDateString(),
                'end'   => $this->period_end?->toDateString(),
            ],
            'status'  => $this->status,

            'automated_metrics' => $this->whenLoaded('metrics', fn() => $this->metrics ? [
                'working_days_count'    => $this->metrics->working_days_count,
                'attendance_rate'       => $this->metrics->attendance_rate,
                'late_rate'             => $this->metrics->late_rate,
                'absence_rate'          => $this->metrics->absence_rate,
                'tasks_submitted_count' => $this->metrics->tasks_submitted_count,
                'on_time_rate'          => $this->metrics->on_time_rate,
                'avg_task_score'        => $this->metrics->avg_task_score,
                'overdue_tasks_count'   => $this->metrics->overdue_tasks_count,
            ] : null),

            'behavioral_rating'  => $this->behavioral_rating,
            'manager_notes'      => $this->manager_notes,
            'next_quarter_goals' => $this->next_quarter_goals,

            'relevant_notes' => EmployeeNoteResource::collection(
                $this->whenLoaded('relevantNotes')
            ),

            'final_score'  => $this->final_score,
            'rating_label' => $this->rating_label,

            'hr_notes' => $this->when(
                $this->status === PerformanceEvaluation::STATUS_APPROVED
                    || $request->user()?->hasAnyRole(['HR', 'admin']),
                $this->hr_notes
            ),

            'employee' => $this->whenLoaded('employee', fn() => $this->employee ? [
                'id'   => $this->employee->id,
                'name' => $this->employee->full_name,
            ] : null),

            'manager' => $this->whenLoaded('manager', fn() => $this->manager ? [
                'id'   => $this->manager->id,
                'name' => $this->manager->full_name,
            ] : null),

            'hr_reviewer' => $this->whenLoaded('hrReviewer', fn() => $this->hrReviewer ? [
                'id'   => $this->hrReviewer->id,
                'name' => $this->hrReviewer->full_name,
            ] : null),

            'created_at' => $this->created_at,
        ];
    }
}
