<?php

namespace App\Http\Resources\job_posting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->when($this->id, $this->id),
            'job_title' => $this->job_title,
            'description' => $this->description,
            'status'     => $this->when($this->status, $this->status),
            'experience' => $this->whenLoaded('requisition', fn() => $this->requisition->experience),
            'department' => $this->whenLoaded('requisition', fn() => $this->requisition->department->name),
            'skills' => $this->whenLoaded('requisition', fn() => $this->requisition->skills->pluck('name')),
            'posted_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'candidates_count' => $this->when($this->relationLoaded('candidates'), fn() => $this->candidates->count()),
            'candidates' => $this->when(
                $this->relationLoaded('candidates'),
                fn() => $this->candidates->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'status' => $c->status,
                ])
            ),
            'requisition' => $this->when($this->relationLoaded('requisition'), fn() => [
                'id' => $this->requisition->id,
                'requested_by' => $this->requisition->requestedBy?->full_name,
            ]),
        ];
    }
}
