<?php

namespace App\Http\Requests\interview;

use App\Models\Interview;
use Illuminate\Foundation\Http\FormRequest;
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
            // التحقق من عدم وجود تكرار في الرتب
            $ranks = array_column($ranking, 'rank');
            if (count($ranks) !== count(array_unique($ranks))) {
                $v->errors()->add('ranking', 'Duplicate ranks are not allowed.');

                return;
            }
            // التحقق من أن جميع المقابلات تنتمي لنفس الوظيفة وحالتها "done"
            $interviewIds = array_column($ranking, 'interview_id');
            $valid = Interview::whereIn('id', $interviewIds)
                ->where('job_posting_id', $jobPosting->id)
                ->where('status', 'done')
                ->count();
            // إذا كان عدد المقابلات الصالحة لا يساوي عدد المقابلات في الترتيب، فهذا يعني أن هناك مقابلة غير صالحة
            if ($valid !== count($interviewIds)) {
                $v->errors()->add('ranking', 'Some interviews are not completed or do not belong to this job posting.');
            }
        });
    }
}
