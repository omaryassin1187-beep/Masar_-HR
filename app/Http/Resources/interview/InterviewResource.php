<?php

namespace App\Http\Resources\interview;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at->format('Y-m-d H:i'),
            'location_type' => $this->location_type,
            'location_details' => $this->location_details,

            'rate' => $this->rate,
            'rate_label' => $this->rate ? $this->rateLabel() : null,
            'notes' => $this->notes,
            'rank' => $this->rank,

            'candidate' => [
                'id' => $this->candidate->id,
                'full_name' => $this->candidate->full_name,
                'email' => $this->candidate->email,
            ],

            'interviewer' => [
                'id' => $this->interviewer->id,
                'full_name' => $this->interviewer->full_name,
            ],

            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
