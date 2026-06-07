<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PrefillJobRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $jobRequisition = $this->route('jobRequisition');
        if (! $this->user()->hasRole('HR')) {
            return false;
        }

        return $jobRequisition->status === 'pending';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    protected function failedAuthorization()
    {
        $jobRequisition = $this->route('jobRequisition');

        $messages = [
            'approved' => 'This requisition has already been approved and converted to a job posting.',

            'rejected' => 'This requisition has been rejected and cannot be processed.',
        ];

        throw new HttpResponseException(
            response()->json([
                'message' => $messages[$jobRequisition->status] ?? 'Unable to process this requisition.',
            ], 422)
        );
    }
}
