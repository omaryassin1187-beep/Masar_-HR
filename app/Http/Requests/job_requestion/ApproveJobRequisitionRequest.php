<?php

namespace App\Http\Requests\job_requestion;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApproveJobRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }

    // hrهي الدالة وظيفتها انو لما
    // مايعدل شي ع العنوان والوصف
    // ف بجيب المعلومات من طلب التوظيف الاصلي
    protected function prepareForValidation(): void
    {
        $requisition = $this->route('jobRequisition');

        if (! $this->has('job_title')) {
            $this->merge(['job_title' => $requisition->job_title]);
        }

        if (! $this->has('description')) {
            $this->merge(['description' => $requisition->description]);
        }
    }
}
