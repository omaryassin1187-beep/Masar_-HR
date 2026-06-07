<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
           'birth_date'=>'date',
           'gender'=>'in:male,female',
           'phone_number'=>'numeric|digits:10',
           'address'=>'string',
           'picture'=>'image|mimes:jpg,jpeg,png|max:2048'
        ];
    }

    public function messages()
{
    return [
        // birth_date field messages
        
        'birth_date.date' => 'The birth date must be a valid date.',

        // gender field messages
       
        'gender.in' => 'The gender must be either male or female.',

        // phone_number field messages
        
        'phone_number.numeric' => 'The phone number must contain only numbers.',
        'phone_number.digits' => 'The phone number must be exactly 10 digits.',

        // address field messages
        
        'address.string' => 'The address must be a valid string.',

        // picture field messages
        'picture.image' => 'The file must be an image.',
        'picture.mimes' => 'The image must be a file of type: jpg, jpeg, or png.',
        'picture.max' => 'The image size must not exceed 2 megabytes.',

        // user_id field messages
        // 'user_id.integer' => 'The user ID must be an integer.',
        // 'user_id.exists' => 'The selected user does not exist in the database.',
    ];
}
}
