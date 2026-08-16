<?php

namespace App\Http\Requests\Resignation;

use App\Models\Resignation;
use Illuminate\Foundation\Http\FormRequest;

class ClassifyResignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hr_classification' => ['required', 'in:mutual_consent,breach_by_company,breach_by_employee'],
            'hr_classification_notes' => ['required', 'string', 'max:2000'],
        ];
    }
}
