<?php

namespace App\Http\Requests\interview;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'interviewed_by' => ['nullable', 'integer', 'exists:users,id'],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],
            'location_type' => ['required', 'in:online,on_site'],
            'location_details' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'The interview date must be in the future.',
            'candidate_id.exists' => 'Candidate not found.',
            'interviewed_by.exists' => 'The specified interviewer does not exist.',
            'location_type.in' => 'Location type must be online or on_site.',
            'location_details.required' => 'Please provide a location or link.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $scheduledAt = $this->input('scheduled_at');
            if (!$scheduledAt) {
                return;
            }

            $settings = Setting::first();
            $time = Carbon::parse($scheduledAt)->format('H:i:s');
            $date = Carbon::parse($scheduledAt);

            // ✅ التحقق من وقت المقابلة
            if ($time < $settings->expected_check_in || $time > $settings->expected_check_out) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'Interview must be scheduled between ' . $settings->expected_check_in . ' and ' . $settings->expected_check_out
                    ], 422)
                );
            }

            // ✅ التحقق من أيام العطلة
            $weekendDays = $settings->weekend_days;
            $dayName = strtolower($date->format('l'));

            if (in_array($dayName, $weekendDays)) {
                throw new HttpResponseException(
                    response()->json([
                        'success' => false,
                        'message' => 'Interviews cannot be scheduled on weekends (' . implode(', ', $weekendDays) . ').'
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
