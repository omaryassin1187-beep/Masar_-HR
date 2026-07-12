<?php

namespace App\Http\Requests\Complaints;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ComplaintRespondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('respond', Complaint::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'hr_note' => ['required', 'string', 'min:2'],
            'status' => ['required', 'in:resolved,rejected'],
        ];
    }

      protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }

protected function failedAuthorization()
{
    abort(403, 'This action is unauthorized.');
}

    public function messages(): array
    {
        return [
            'hr_note.min' => 'Please provide a detailed response (at least 2 characters).',
        ];
    }
}
