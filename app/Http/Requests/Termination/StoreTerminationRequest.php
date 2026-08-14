<?php

namespace App\Http\Requests\Termination;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTerminationRequest extends FormRequest
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

            /*
            |--------------------------------------------------------------------------
            | Common fields
            |--------------------------------------------------------------------------
            */

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'type' => [
                'required',
                Rule::in([
                    'standard',
                    'immediate',
                ]),
            ],

            'termination_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],


            /*
            |--------------------------------------------------------------------------
            | Immediate termination
            |--------------------------------------------------------------------------
            */

            'subtype' => [
                Rule::requiredIf(
                    fn() => $this->input('type') === 'immediate'
                ),

                Rule::in([
                    'misconduct',
                    'company_composition',
                    'mutual_agreement',
                ]),

                Rule::prohibitedIf(
                    fn() => $this->input('type') === 'standard'
                ),
            ],

            'compensation_amount' => [
                'nullable',
                'numeric',
                'min:0',

                Rule::requiredIf(
                    fn() => in_array(
                        $this->input('subtype'),
                        [
                            'company_composition',
                            'mutual_agreement',
                        ]
                    )
                ),

                Rule::prohibitedIf(
                    fn() => !in_array(
                        $this->input('subtype'),
                        [
                            'company_composition',
                            'mutual_agreement',
                        ]
                    )
                ),
            ],

            'legal_reason' => [
                'nullable',
                'string',

                Rule::requiredIf(
                    fn() => $this->input('subtype') === 'misconduct'
                ),

                Rule::prohibitedIf(
                    fn() => $this->input('subtype') !== 'misconduct'
                ),
            ],

            'documents' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Common fields
            |--------------------------------------------------------------------------
            */

            'user_id.required' =>
            'The employee is required.',

            'user_id.integer' =>
            'The employee ID must be a valid integer.',

            'user_id.exists' =>
            'The selected employee does not exist.',


            'type.required' =>
            'The termination type is required.',

            'type.in' =>
            'The termination type must be either standard or immediate.',


            'termination_date.required' =>
            'The termination date is required.',

            'termination_date.date' =>
            'The termination date must be a valid date.',

            'termination_date.after_or_equal' =>
            'The termination date cannot be in the past.',


            /*
            |--------------------------------------------------------------------------
            | Immediate termination
            |--------------------------------------------------------------------------
            */

            'subtype.required' =>
            'The immediate termination subtype is required.',

            'subtype.in' =>
            'The immediate termination subtype is invalid.',

            'subtype.prohibited' =>
            'The termination subtype cannot be provided for standard termination.',


            'compensation_amount.required' =>
            'The compensation amount is required for this termination subtype.',

            'compensation_amount.numeric' =>
            'The compensation amount must be a valid number.',

            'compensation_amount.min' =>
            'The compensation amount cannot be negative.',

            'compensation_amount.prohibited' =>
            'The compensation amount is not allowed for this termination subtype.',


            'legal_reason.required' =>
            'The legal reason is required for misconduct termination.',

            'legal_reason.string' =>
            'The legal reason must be a valid text.',

            'legal_reason.prohibited' =>
            'The legal reason is not allowed for this termination subtype.',


            'documents.file' =>
            'The document must be a valid file.',

            'documents.mimes' =>
            'The document must be a PDF, Word document, or image.',

            'documents.max' =>
            'The document size must not exceed 10MB.',


            'notes.string' =>
            'The notes must be a valid text.',
        ];
    }
}
