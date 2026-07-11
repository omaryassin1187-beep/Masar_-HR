<?php
namespace App\Http\Requests\sign_contract;

use Illuminate\Foundation\Http\FormRequest;

class HrSignContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['signature' => ['required', 'string', 'starts_with:data:image/']];
    }
}
