<?php

namespace App\Http\Requests;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOfferRequest extends FormRequest
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
            'candidate_id'         => ['required', 'integer', 'exists:candidates,id'],
            'hour_price'           => ['required', 'numeric', 'min:0.01'],
            'start_date'           => ['required', 'date', 'after:today'],
            'weekend_days'         => ['required', 'array', 'min:1', 'max:3'],
            'weekend_days.*'       => ['required', 'in:friday,saturday,sunday,monday,tuesday,wednesday,thursday'],
            'working_hour_per_day' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
    public function messages(): array
    {
        return [
            'start_date.after'          => 'Start date must be after today.',
            'weekend_days.min'          => 'At least one weekend day is required.',
            'weekend_days.max'          => 'No more than 3 weekend days allowed.',
            'weekend_days.*.in'         => 'Invalid weekend day.',
            'working_hour_per_day.max'  => 'Working hours cannot exceed 12 per day.',
            'hour_price.min'            => 'Hourly rate must be greater than zero.',
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $candidate  = Candidate::find($this->input('candidate_id'));
            $jobPosting = $this->route('jobPosting');

            if (! $candidate) {
                return;
            }

            if (! in_array($candidate->status, ['interviewed', 'qualified'])) {
                $v->errors()->add(
                    'candidate_id',
                    'The candidate has not passed the interview stage yet.'
                );
                return;
            }

            $hasDoneInterview = Interview::where('candidate_id', $candidate->id)
                ->where('job_posting_id', $jobPosting->id)
                ->where('status', 'done')
                ->exists();

            if (! $hasDoneInterview) {
                $v->errors()->add(
                    'candidate_id',
                    'The candidate must have a completed interview before receiving an offer.'
                );
                return;
            }

            $alreadyHasOffer = Offer::where('candidate_id', $candidate->id)
                ->where('job_posting_id', $jobPosting->id)
                ->where('status', 'pending')
                ->exists();

            if ($alreadyHasOffer) {
                $v->errors()->add(
                    'candidate_id',
                    'A pending offer already exists for this candidate.'
                );
            }
        });
    }
}
