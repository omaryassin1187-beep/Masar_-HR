<?php

namespace App\Http\Requests\job_posting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_title.required' => 'Job title cannot be empty.',
            'job_title.max' => 'Job title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 5000 characters.',
        ];
    }
}
