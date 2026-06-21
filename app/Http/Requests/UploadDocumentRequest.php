<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        // فقط الـ employees يرفعون وثائق الـ onboarding
        return $this->user() && $this->user()->hasRole('employee');
    }

    public function rules(): array
    {
        return [
            'id_card'      => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'photo'        => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'bank_info'    => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'professional_files'   => ['nullable', 'array'],
            'professional_files.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }
}
