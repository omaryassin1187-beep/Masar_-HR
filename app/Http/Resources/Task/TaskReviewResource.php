<?php

namespace App\Http\Resources\Task;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'score'   => $this->score,
            'comment' => $this->comment,
            'status'  => $this->status,

            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->full_name,
            ] : null),

            'created_at' => $this->created_at,
        ];
    }
}
