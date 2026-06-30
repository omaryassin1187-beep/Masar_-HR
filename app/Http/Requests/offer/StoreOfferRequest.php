<?php

namespace App\Http\Requests\offer;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'hour_price' => ['required', 'numeric', 'min:0.01'],
            'start_date' => ['required', 'date', 'after:today'],
            'weekend_days' => ['required', 'array', 'min:1', 'max:3'],
            'weekend_days.*' => ['required', 'in:friday,saturday,sunday,monday,tuesday,wednesday,thursday'],
            'working_hours_per_day' => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after' => 'Start date must be after today.',
            'weekend_days.min' => 'At least one weekend day is required.',
            'weekend_days.max' => 'No more than 3 weekend days allowed.',
            'weekend_days.*.in' => 'Invalid weekend day.',
            'working_hours_per_day.max' => 'Working hours cannot exceed 12 per day.',
            'hour_price.min' => 'Hourly rate must be greater than zero.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $candidate = Candidate::find($this->input('candidate_id'));
            $jobPosting = $this->route('jobPosting');

            if (!$candidate) {
                return;
            }

            // ✅ 1️⃣ Check if candidate is already an employee
            if (User::where('email', $candidate->email)->exists()) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'This candidate is already an employee in the system. Cannot send an offer.'
                    ], 422)
                );
            }

            // ✅ 2️⃣ Check if candidate has an accepted offer without signature
            $hasAcceptedOfferWithoutSignature = Offer::where('candidate_id', $candidate->id)
                ->where('status', 'accepted')
                ->whereHas('contracts', function ($query) {
                    $query->whereNull('candidate_signed_at');
                })
                ->exists();

            if ($hasAcceptedOfferWithoutSignature) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'This candidate has already accepted a previous offer but hasn\'t signed the contract yet.'
                    ], 422)
                );
            }

            // ✅ 3️⃣ Check if candidate has a pending offer
            $hasPendingOffer = Offer::where('candidate_id', $candidate->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingOffer) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'This candidate already has a pending offer. Please wait for their response first.'
                    ], 422)
                );
            }

            // ✅ 4️⃣ Check if candidate already has an offer for this job
            $alreadyHasOfferForJob = Offer::where('candidate_id', $candidate->id)
                ->where('job_posting_id', $jobPosting->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->exists();

            if ($alreadyHasOfferForJob) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'This candidate already has an existing offer for this job posting.'
                    ], 422)
                );
            }

            // ✅ 5️⃣ Check if candidate has passed interview stage
            if (!in_array($candidate->status, ['interviewed', 'qualified'])) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'The candidate has not passed the interview stage yet.'
                    ], 422)
                );
            }

            // ✅ 6️⃣ Check if candidate has completed interview
            $hasDoneInterview = Interview::where('candidate_id', $candidate->id)
                ->where('job_posting_id', $jobPosting->id)
                ->where('status', 'done')
                ->exists();

            if (!$hasDoneInterview) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'The candidate must have a completed interview before receiving an offer.'
                    ], 422)
                );
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }
}
