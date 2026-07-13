<?php

namespace App\Http\Requests\hourly_leave_request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHourlyLeaveRequest extends FormRequest
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
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|min:5|max:500',
        ];
    }

        public function messages(): array
    {
        return [
            'date.required' => 'The date field is required.',
            'date.date' => 'Please enter a valid date.',
            'date.after_or_equal' => 'The date must be today or a future date.',

            'start_time.required' => 'The start time field is required.',
            'start_time.date_format' => 'The start time must be in 24-hour format (e.g., 09:00).',

            'end_time.required' => 'The end time field is required.',
            'end_time.date_format' => 'The end time must be in 24-hour format (e.g., 17:00).',
            'end_time.after' => 'The end time must be after the start time.',

            'reason.required' => 'The reason field is required.',
            'reason.string' => 'The reason must be a valid text.',
            'reason.min' => 'The reason must be at least 5 characters.',
            'reason.max' => 'The reason may not be greater than 500 characters.',
        ];
    }
}
