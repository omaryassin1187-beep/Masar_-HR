<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'interviewed_by' => ['required', 'integer', 'exists:users,id'],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    $settings = Setting::first();
                    $time = Carbon::parse($value)->format('H:i:s');
                    $date = Carbon::parse($value);

                    if ($time < $settings->expected_check_in || $time > $settings->expected_check_out) {
                        $fail('Interview must be scheduled between ' . $settings->expected_check_in . ' and ' . $settings->expected_check_out);
                    }
                    $weekendDays = $settings->weekend_days; // بالفعل array من الـ cast
                    $dayName = strtolower($date->format('l')); // "friday"

                    if (in_array($dayName, $weekendDays)) {
                        $fail('Interviews cannot be scheduled on weekends (' . implode(', ', $weekendDays) . ').');
                    }
                },
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
}
