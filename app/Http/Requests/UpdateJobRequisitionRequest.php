<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $requisition = $this->route('jobRequisition');

        return $this->user()->can('update', $requisition);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'experience' => ['sometimes', 'integer', 'min:0'],
            'skills' => ['sometimes', 'array', 'min:1'],
            'skills.*' => ['integer', 'exists:skills,id'],

        ];
    }

    public function messages(): array
    {
        return [
            'job_title.string' => 'Job title must be a string.',
            'job_title.max' => 'Job title is too long.',

            'description.string' => 'Description must be a string.',

            'experience.integer' => 'Experience must be a number.',
            'experience.min' => 'Experience cannot be negative.',

            'skills.array' => 'Skills must be an array.',
            'skills.min' => 'At least one skill is required when updating skills.',
            'skills.*.integer' => 'Each skill must be a valid ID.',
            'skills.*.exists' => 'One or more selected skills do not exist.',
        ];
    }
}
