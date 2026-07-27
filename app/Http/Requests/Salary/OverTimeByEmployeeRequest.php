<?php

namespace App\Http\Requests\Salary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;
use Carbon\Carbon;

class OverTimeByEmployeeRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

            'date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {

                    $attendanceService = app(\App\Services\AttendanceService::class);

                    // إذا كان اليوم عطلة فلا نتحقق من وقت البداية
                    if (!$attendanceService->isWorkingDay($this->date)) {
                        return;
                    }

                    $expectedCheckout = Carbon::createFromFormat(
                        'H:i:s',
                        Setting::instance()->expected_check_out
                    );

                    $startTime = Carbon::createFromFormat('H:i', $value);

                    if ($startTime->lt($expectedCheckout)) {
                        $fail('Overtime cannot start before the official checkout time on working days.');
                    }
                },
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'date.required' => 'Date is required.',
            'date.date' => 'Invalid date.',
            'date.after_or_equal' => 'The overtime date cannot be in the past.',

            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',

            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',


            'notes.string' => 'Notes must be a valid text.',
            'notes.max' => 'Notes may not exceed 1000 characters.',
        ];
    }
}
