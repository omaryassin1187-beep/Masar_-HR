<?php

namespace App\Http\Requests\interview;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInterviewResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('interview')->status === 'scheduled';
    }

    public function rules(): array
    {
        return [
            'rate' => ['required', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rate.required' => 'Rating is required.',
            'rate.min' => 'Minimum rating is 1.',
            'rate.max' => 'Maximum rating is 10.',
        ];
    }

 protected function failedAuthorization()
{
    throw new HttpResponseException(
        response()->json([
            'success' => false,
            'message' => 'Cannot record result: the interview is not in scheduled status.'
        ], 403)
    );
}
}
