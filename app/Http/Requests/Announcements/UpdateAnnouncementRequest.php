<?php

namespace App\Http\Requests\Announcements;

use App\Http\Requests\Announcements\Concerns\HasAnnouncementValidationRules;
use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator; // ✅ هذا الصحيح


class UpdateAnnouncementRequest extends FormRequest
{
    use HasAnnouncementValidationRules;


    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('announcement')) ?? false;
    }


    protected function prepareForValidation(): void
    {
        $this->forceManagerAudienceIfNeeded();

        /** @var Announcement|null $announcement */
        $announcement = $this->route('announcement');


        if ($announcement) {
            $this->mergeIfMissing([
                'expires_at' => $announcement->expires_at,
            ]);
        }
    }

    public function rules(): array
    {
        return $this->commonRules(requirePresence: false);
    }

    public function messages(): array
    {
        return $this->announcementValidationMessages();
    }

    protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403)
        );
    }
}
