<?php

namespace App\Http\Requests;

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
            'probation_period_days'   => ['sometimes', 'integer', 'min:1', 'max:365'],
            'weekend_days'            => ['sometimes', 'array', 'min:1', 'max:6'],
            'weekend_days.*'          => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'jurisdiction'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'termination_notice_days' => ['sometimes', 'integer', 'min:1', 'max:180'],
            'expected_check_in'       => ['sometimes', 'date_format:H:i'],
            'expected_check_out'      => ['sometimes', 'date_format:H:i', 'after:expected_check_in'],
            'sick_leave_days'         => ['sometimes', 'integer', 'min:0', 'max:365'],
            'annual_leave_days'       => ['sometimes', 'integer', 'min:0', 'max:365'],
            'currency'                => ['sometimes', 'string', 'size:3'],
            'grace_period'            => ['sometimes', 'integer', 'min:0', 'max:120'],
        ];
    }
    public function messages(): array
    {
        return [
            'weekend_days.*.in'              => 'Invalid day. Accepted values: monday through sunday.',
            'expected_check_out.after'       => 'Check-out time must be after check-in time.',
            'currency.size'                  => 'Currency code must be exactly 3 characters (e.g. USD, SYP).',
            'probation_period_days.max'      => 'Probation period cannot exceed 365 days.',
            'termination_notice_days.max'    => 'Termination notice period cannot exceed 180 days.',
            'grace_period.max'               => 'Grace period cannot exceed 120 minutes.',
            'expected_check_in.date_format'  => 'Check-in time must be in HH:MM format (e.g. 09:00).',
            'expected_check_out.date_format' => 'Check-out time must be in HH:MM format (e.g. 17:00).',
        ];
    }
}
