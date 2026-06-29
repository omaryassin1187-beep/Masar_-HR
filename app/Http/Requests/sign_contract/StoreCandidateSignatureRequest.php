<?php
namespace App\Http\Requests\sign_contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['signature' => ['required', 'string', 'starts_with:data:image/']];
    }

    public function messages(): array
    {
        return [
            'signature.required'    => 'Signature is required',
            'signature.starts_with' => 'Invalid signature format',
        ];
    }
}
