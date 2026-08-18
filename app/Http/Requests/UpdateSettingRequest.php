<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            // General Work & Leave Settings
            'probation_period_days'          => ['sometimes', 'integer', 'min:1', 'max:365'],
            'weekend_days'                   => ['sometimes', 'array', 'min:1', 'max:6'],
            'weekend_days.*'                 => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'jurisdiction'                   => ['sometimes', 'nullable', 'string', 'max:100'],
            'termination_notice_days'        => ['sometimes', 'integer', 'min:1', 'max:180'],
            'expected_check_in'              => ['sometimes', 'date_format:H:i'],
            'expected_check_out'             => ['sometimes', 'date_format:H:i', 'after:expected_check_in'],
            'sick_leave_days'                => ['sometimes', 'integer', 'min:0', 'max:365'],
            'annual_leave_days'              => ['sometimes', 'integer', 'min:0', 'max:365'],
            'currency'                       => ['sometimes', 'string', 'size:3'],
            'grace_period'                   => ['sometimes', 'integer', 'min:0', 'max:120'],

            // Geolocation Settings
            'company_latitude'               => ['sometimes', 'numeric', 'between:-90,90'],
            'company_longitude'              => ['sometimes', 'numeric', 'between:-180,180'],
            'allowed_radius'                 => ['sometimes', 'integer', 'min:10', 'max:10000'],

            // Performance Evaluation Settings
            'eval_task_quality_weight'       => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'eval_task_ontime_weight'        => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'eval_attendance_weight'         => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'eval_salary_increase_threshold' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'eval_min_tenure_days'           => ['sometimes', 'integer', 'min:0', 'max:365'],

            // End of Service Settings
            'end_of_service_months_per_year' => ['sometimes', 'integer', 'min:0', 'max:12'],
        ];
    }

    /**
     * Additional validation logic for checking performance evaluation weights sum.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            // Check total weight only if any of the evaluation weight fields are sent in request
            if (
                array_key_exists('eval_task_quality_weight', $data) ||
                array_key_exists('eval_task_ontime_weight', $data) ||
                array_key_exists('eval_attendance_weight', $data)
            ) {
                $setting = Setting::instance();

                $quality = $data['eval_task_quality_weight'] ?? $setting->eval_task_quality_weight;
                $ontime  = $data['eval_task_ontime_weight']  ?? $setting->eval_task_ontime_weight;
                $attendance = $data['eval_attendance_weight'] ?? $setting->eval_attendance_weight;

                $total = round((float) $quality + (float) $ontime + (float) $attendance, 2);

                if ($total !== 1.00) {
                    $validator->errors()->add(
                        'eval_weights',
                        "The sum of evaluation weights (quality, ontime, attendance) must equal 1.00 (100%). Current total is {$total}."
                    );
                }
            }
        });
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'weekend_days.*.in'               => 'Invalid day. Accepted values: monday through sunday.',
            'expected_check_out.after'        => 'Check-out time must be after check-in time.',
            'currency.size'                   => 'Currency code must be exactly 3 characters (e.g. USD, SYP).',
            'probation_period_days.max'       => 'Probation period cannot exceed 365 days.',
            'termination_notice_days.max'     => 'Termination notice period cannot exceed 180 days.',
            'grace_period.max'                => 'Grace period cannot exceed 120 minutes.',
            'expected_check_in.date_format'   => 'Check-in time must be in HH:MM format (e.g. 09:00).',
            'expected_check_out.date_format'  => 'Check-out time must be in HH:MM format (e.g. 17:00).',
            'company_latitude.between'        => 'Latitude must be between -90 and 90 degrees.',
            'company_longitude.between'       => 'Longitude must be between -180 and 180 degrees.',
            'allowed_radius.min'              => 'Allowed radius must be at least 10 meters.',
            'eval_task_quality_weight.min'    => 'Weight must be between 0.00 and 1.00.',
            'eval_task_quality_weight.max'    => 'Weight must be between 0.00 and 1.00.',
            'eval_task_ontime_weight.min'     => 'Weight must be between 0.00 and 1.00.',
            'eval_task_ontime_weight.max'     => 'Weight must be between 0.00 and 1.00.',
            'eval_attendance_weight.min'      => 'Weight must be between 0.00 and 1.00.',
            'eval_attendance_weight.max'      => 'Weight must be between 0.00 and 1.00.',
            'eval_salary_increase_threshold.max' => 'Salary increase threshold cannot exceed 100%.',
        ];
    }
}
