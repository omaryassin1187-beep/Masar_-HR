<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobRequisitionDetailResource extends JsonResource
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
            'experience' => (int) $this->experience, // Cast لضمان وصوله كـ Integer للـ Front-end
            'status' => $this->status,
            'requested_by' => $this->whenLoaded('requestedBy', function () {
                return [
                    'id' => $this->requestedBy->id,
                    'full_name' => $this->requestedBy->full_name,
                    'status' => $this->requestedBy->status,
                ];
            }),

            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),

            // جلب المهارات المطلوبة وتنظيفها من طوابع الوقت وبيانات جدول الـ Pivot الزائدة
            'skills' => $this->whenLoaded('skills', function () {
                return $this->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                    ];
                });
            }),
        ];
    }
}
