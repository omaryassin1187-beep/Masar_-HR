<?php

namespace App\Http\Requests\candidate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $posting = $this->route('jobPosting');

        return $posting && $posting->status === 'open';
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'This job posting is closed and no longer accepting applications.',
            ], 403)
        );
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
            'additional_skills' => ['nullable', 'string', 'max:1000'],
            'cover_letter' => ['nullable', 'string', 'max:3000'],
            'experience' => ['nullable', 'integer', 'min:0'],
            'more_skill' => ['nullable', 'string', 'max:1000'],

        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email is not valid.',
            'cv.required' => 'CV (PDF) is required.',
            'cv.mimes' => 'CV must be a PDF file.',
            'cv.max' => 'CV must not exceed 5MB.',
            'skill_ids.required' => 'At least one skill is required.',
            'skill_ids.array' => 'Skills must be an array.',
            'skill_ids.*.exists' => 'One of the selected skills does not exist.',
        ];
    }
}
