<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestRequest extends FormRequest
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
            'type' => 'required|in:annual,sick,unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'days_count' => 'required|integer|min:1|max:30',
            'reason' => 'required|string|min:5|max:500',

        ];
    }

    public function messages()
    {
        return [

            'start_date.required' => 'The start date field is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'start_date.after_or_equal' => 'The date must be today or a future date.',

            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either annual or sick or unpaid.',


            'days_count.required' => 'Please enter the number of leave days.',
            'days_count.integer' => 'The leave days count must be a valid number.',
            'days_count.min' => 'Leave days must be at least 1 day.',
            'days_count.max' => 'You cannot request more than 30 leave days.',

            'reason.required' => 'The reason field is required.',
            'reason.string' => 'The reason must be a valid text.',
            'reason.min' => 'The reason must be at least 5 characters.',
            'reason.max' => 'The reason may not be greater than 500 characters.',
        ];
    }
}
