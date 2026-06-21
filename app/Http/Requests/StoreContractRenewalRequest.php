<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractRenewalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('HR');
    }


    public function rules(): array
    {
        return [
            'new_start_date' => [
                'required',
                'date',
                // تاريخ البدء الجديد يجب أن يكون في المستقبل
                'after:today',
            ],
            'new_end_date' => [
                'required',
                'date',
                // تاريخ الانتهاء بعد تاريخ البدء
                'after:new_start_date',
            ],
            'new_hour_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'new_weekend_days' => ['nullable', 'array'],
            'new_working_hours_per_day' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }
}
