<?php

namespace App\Http\Requests\Salary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIncentiveRequest extends FormRequest
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
        'user_id' => [ 'exists:users,id'],
        'date'    => [ 'date'],
        'amount'  => [ 'numeric', 'min:0.01'],
        'reason'  => [ 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
           
            'user_id.exists'   => 'The selected employee does not exist.',

            
            'date.date'        => 'The Incentive date must be a valid date.',

            
            'amount.numeric'   => 'Incentive amount must be a number.',
            'amount.min'       => 'Incentive amount must be greater than zero.',

            
            'reason.string'    => 'Incentive reason must be a string.',
            'reason.max'       => 'Incentive reason may not be greater than 255 characters.',
        ];
    }
}
