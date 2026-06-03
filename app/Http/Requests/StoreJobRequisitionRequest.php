<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'experience' => ['required', 'integer', 'min:0'],
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['integer', 'exists:skills,id'],
        ];

    }

    public function messages(): array
    {
        return [
            'job_title.required' => 'Job title is required.',
            'description.required' => 'Description is required.',
            'experience.required' => 'Experience level is required.',
            'skills.required' => 'At least one skill is required.',
            'skills.min' => 'At least one skill is required.',
            'skills.*.integer' => 'Each skill must be a valid ID.',
            'skills.*.exists' => 'One or more selected skills do not exist.',
        ];
    }
}
