<?php

namespace App\Http\Requests\Salary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IncreaseHourlyRateRequest extends FormRequest
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
            'hour_price' => ['required', 'numeric', 'gt:0'],
            'reason'     => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'hour_price.required' => 'The hourly rate is required.',
            'hour_price.numeric'  => 'The hourly rate must be a valid number.',
            'hour_price.gt'       => 'The hourly rate must be greater than zero.',

            'reason.string'       => 'The reason must be a valid text.',
            'reason.max'          => 'The reason may not be greater than 255 characters.',
        ];
    }
}
