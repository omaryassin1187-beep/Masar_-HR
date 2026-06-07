<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequisitionlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_title' => $this->job_title,
            'experience' => $this->experience,
            'status' => $this->status,
            'created_at' => $this->created_at->toDateString(),
            'department' => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ],
            'requested_by' => [
                'id' => $this->requestedBy->id,
                'full_name' => $this->requestedBy->full_name,
            ],
            'skills_count' => $this->skills->count(),
            'is_posted' => $this->jobPosting()->exists(),
        ];
    }
}
