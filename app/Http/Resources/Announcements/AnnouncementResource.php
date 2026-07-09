<?php

namespace App\Http\Resources\Announcements;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'priority' => $this->priority,
            'target_audience' => $this->target_audience,

            // استخدام $this->relationLoaded لمنع حدوث أي Lazy Loading بالخطأ
            'department' => $this->when($this->relationLoaded('department') && $this->department, function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),

            'author' => $this->when($this->relationLoaded('author') && $this->author, function () {
                return [
                    'id' => $this->author->id,
                    'full_name' => $this->author->full_name,
                ];
            }),

            'status' => $this->status,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
