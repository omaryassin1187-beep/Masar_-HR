<?php

namespace App\Http\Resources\Recruitment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequisitionResource extends JsonResource
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
            'title' => $this->job_title,
            'description' => $this->description,
            'experience' => $this->experience,
            'status' => $this->status,
            'department' => $this->department->name ?? null,
            'requested_by' => $this->requestedBy->full_name ?? null,
            'skills' => $this->skills->pluck('name'),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
