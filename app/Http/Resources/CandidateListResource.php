<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'status' => $this->status,
            'matched_skills_count' => $this->matched_skills_count ?? 0,
            'skills_count' => $this->skills_count ?? 0,
            'applied_at' => $this->created_at->toDateString(),
            'more_skill' => ! empty($this->more_skill),
        ];
    }
}
