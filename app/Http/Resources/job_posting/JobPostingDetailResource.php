<?php

namespace App\Http\Resources\job_posting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingDetailResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'experience' => $this->whenLoaded('requisition', fn () => $this->requisition->experience),
            'department' => $this->whenLoaded('requisition', fn () => $this->requisition->department->name),
            'skills' => $this->whenLoaded('requisition', fn () => $this->requisition->skills->pluck('name')),
            'posted_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
