<?php

namespace App\Http\Requests\Resignation;

use App\Models\Resignation;
use Illuminate\Foundation\Http\FormRequest;

class StoreResignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Resignation::class);
    }

    public function rules(): array
    {
        return [
            'type'   => ['required', 'in:with_notice,immediate'],
            'reason' => ['required_if:type,immediate', 'nullable', 'string', 'max:2000'],
            'documents'   => ['nullable', 'array'],
            'documents.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,docx'],
        ];
    }
}
