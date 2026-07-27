<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class TaskSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'task_id' => $this->task_id,
            'notes'   => $this->notes,

            'attachment_url' => $this->attachment_path
                ? URL::temporarySignedRoute(
                    'task-submissions.attachment',
                    now()->addMinutes(30),
                    ['submission' => $this->id]
                )
                : null,

            'submitter' => $this->whenLoaded('submitter', fn () => $this->submitter ? [
                'id'   => $this->submitter->id,
                'name' => $this->submitter->full_name,
            ] : null),

            'review' => $this->whenLoaded('review', fn () => $this->review
                ? new TaskReviewResource($this->review)
                : null),

            'created_at' => $this->created_at,
        ];
    }
}
