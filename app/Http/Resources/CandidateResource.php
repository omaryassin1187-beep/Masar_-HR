<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'cover_letter' => $this->cover_letter,
            'more_skill' => $this->more_skill,
            'status' => $this->status,
            'experience' => $this->experience,
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'matched_skills' => SkillResource::collection(
                $this->when($this->relationLoaded('skills'), fn () => $this->matched_skills)
            ),
            'cv_url' => $this->when(
                $this->relationLoaded('documents'),
                fn () => $this->getCvUrl()
            ),
            'job_posting' => new JobPostingListResource($this->whenLoaded('jobPosting')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }

    private function getCvUrl(): ?string
    {
        $cvDoc = $this->documents->where('type', 'cv')->first();
        if (! $cvDoc) {
            return null;
        }

        return route('candidates.cv', [
            'candidate' => $this->id,
            'expires' => now()->addMinutes(30)->timestamp,
            'signature' => hash_hmac('sha256', $this->id.'|'.$cvDoc->file_path, config('app.key')),
        ]);
    }

    // توليد توقيع URL لمنع التلاعب به
    private function generateSignature(int $candidateId, string $path): string
    {
        return hash_hmac('sha256', $candidateId.'|'.$path, config('app.key'));
    }
}
