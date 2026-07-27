<?php

namespace App\Http\Requests\profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 💡 لمسة سحرية: تحويل الفواصل القادمة من الفرونت إند لضمان توافقها مع مفسر التواريخ
     */
    protected function prepareForValidation()
    {
        if ($this->has('birth_date') && $this->birth_date) {
            // استبدال / بـ - وتمريرها نظيفة للـ Validation والكنترولر
            $formattedDate = str_replace('/', '-', $this->birth_date);
            
            $this->merge([
                'birth_date' => $formattedDate,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
           'birth_date'   => 'required|date', // أصبحت آمنة تماماً الآن بعد التنظيف
           'gender'       => 'required|in:male,female',
           'phone_number' => 'numeric|required|digits:10',
           'address'      => 'required|string',
           'picture'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'birth_date.required' => 'The birth date field is required.',
            'birth_date.date'     => 'The birth date must be a valid date.',
            
            'gender.required'     => 'The gender field is required.',
            'gender.in'           => 'The gender must be either male or female.',
            
            'phone_number.required' => 'The phone number field is required.',
            'phone_number.numeric'  => 'The phone number must contain only numbers.',
            'phone_number.digits'   => 'The phone number must be exactly 10 digits.',
            
            'address.required'    => 'The address field is required.',
            'address.string'      => 'The address must be a valid string.',
            
            'picture.image'       => 'The file must be an image.',
            'picture.mimes'       => 'The image must be a file of type: jpg, jpeg, or png.',
            'picture.max'         => 'The image size must not exceed 2 megabytes.',
        ];
    }
}