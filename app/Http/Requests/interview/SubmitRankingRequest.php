<?php

namespace App\Http\Requests\interview;

use App\Models\Interview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class SubmitRankingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ranking' => ['required', 'array', 'min:1'],
            'ranking.*.interview_id' => ['required', 'integer', 'exists:interviews,id'],
            'ranking.*.rank' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'ranking.required' => 'The ranking list is required.',
            'ranking.*.interview_id.exists' => 'One of the interviews does not exist.',
            'ranking.*.rank.min' => 'Rank must start at 1.',
        ];
    }

    public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $v) {
        $ranking = $this->input('ranking', []);
        $jobPosting = $this->route('jobPosting');

      // 1️⃣ التحقق من عدم وجود تكرار في الرتب
        $ranks = array_column($ranking, 'rank');
        if (count($ranks) !== count(array_unique($ranks))) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Duplicate ranks are not allowed.'
                ], 422)
            );}

        // 2️⃣ التحقق من صيانة المقابلات وهويتها
        $interviewIds = array_column($ranking, 'interview_id');
        $valid = Interview::whereIn('id', $interviewIds)
            ->where('job_posting_id', $jobPosting->id)
            ->where('status', 'done')
            ->count();


        if ($valid !== count($interviewIds)) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Some interviews are not completed or do not belong to this job posting.'
                ], 422)
            );
        }
    });
}
}
