<?php

namespace App\Http\Requests\Task;

use App\Models\TaskReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'  => ['required', Rule::in([
                TaskReview::STATUS_APPROVED,
                TaskReview::STATUS_REJECTED,
            ])],
            'score'   => ['required_if:status,' . TaskReview::STATUS_APPROVED, 'integer', 'min:0', 'max:100'],
            'comment' => ['required_if:status,' . TaskReview::STATUS_REJECTED, 'nullable', 'string', 'max:2000'],
        ];
    }
}
