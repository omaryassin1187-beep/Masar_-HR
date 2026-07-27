<?php

namespace App\Http\Requests\Evaluation;

use App\Models\EmployeeNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([
                EmployeeNote::TYPE_POSITIVE,
                EmployeeNote::TYPE_NEGATIVE,
                EmployeeNote::TYPE_GOAL,
                EmployeeNote::TYPE_GENERAL,
            ])],
            'content' => ['required', 'string', 'max:2000'],
        ];
    }
}
