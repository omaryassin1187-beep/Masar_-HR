<?php

namespace App\Http\Resources\Task;

use App\Models\Task;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => $this->priority,
            'status'      => $this->status,
            'due_date'    => $this->due_date?->toDateString(),

            'assigned_at'  => $this->assigned_at,
            'submitted_at' => $this->submitted_at,
            'reviewed_at'  => $this->reviewed_at,
            'score'        => $this->score,

            'rejection_reason' => $this->when(
                $this->status === Task::STATUS_REJECTED,
                $this->rejection_reason
            ),

            'is_overdue' => $this->isOverdue(),

            'creator'  => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id'   => $this->creator->id,
                'name' => $this->creator->full_name,
            ] : null),

            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id'   => $this->assignee->id,
                'name' => $this->assignee->full_name,
            ] : null),

            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->full_name,
            ] : null),

            'latest_submission' => $this->whenLoaded('latestSubmission', fn () => $this->latestSubmission
                ? new TaskSubmissionResource($this->latestSubmission)
                : null),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
