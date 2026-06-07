<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHourlyLeaveRequest extends FormRequest
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
            'date' => 'date|after_or_equal:today',
            'start_time' => 'date_format:H:i|required_with:end_time',
            'end_time' => 'date_format:H:i|after:start_time|required_with:start_time',
            'reason' => 'string|min:5|max:500',
        ];
    }

        public function messages(): array
    {
        return [
           
            'date.date' => 'Please enter a valid date.',
            'date.after_or_equal' => 'The date must be today or a future date.',
            
            
            'start_time.date_format' => 'The start time must be in 24-hour format (e.g., 09:00).',
            
            
            'end_time.date_format' => 'The end time must be in 24-hour format (e.g., 17:00).',
            'end_time.after' => 'The end time must be after the start time.',
            
            
            'reason.string' => 'The reason must be a valid text.',
            'reason.min' => 'The reason must be at least 5 characters.',
            'reason.max' => 'The reason may not be greater than 500 characters.',
        ];
    }
}
