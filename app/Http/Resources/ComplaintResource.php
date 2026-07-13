<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'route_type' => $this->route_type,
            'status' => $this->status,
            'hr_note' => $this->hr_note,

            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'full_name' => $this->author->full_name,
            ]),

            'subject' => $this->whenLoaded('subject', fn () => [
                'id' => $this->subject->id,
                'full_name' => $this->subject->full_name,
            ]),

            'resolver' => $this->whenLoaded('resolver', fn () => $this->resolver ? [
                'id' => $this->resolver->id,
                'full_name' => $this->resolver->full_name,
            ] : null),

            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
