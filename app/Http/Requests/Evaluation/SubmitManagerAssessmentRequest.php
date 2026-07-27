<?php

namespace App\Http\Requests\Evaluation;

use App\Models\PerformanceEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitManagerAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'behavioral_rating' => ['required', Rule::in([
                PerformanceEvaluation::RATING_EXCELLENT,
                PerformanceEvaluation::RATING_GOOD,
                PerformanceEvaluation::RATING_AVERAGE,
                PerformanceEvaluation::RATING_POOR,
            ])],
            'manager_notes'        => ['nullable', 'string', 'max:2000'],
            'next_quarter_goals'   => ['nullable', 'array', 'max:10'],
            'next_quarter_goals.*' => ['string', 'max:255'],
        ];
    }
}
