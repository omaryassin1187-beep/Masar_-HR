<?php

namespace App\Http\Requests\Announcements;

use App\Http\Requests\Announcements\Concerns\HasAnnouncementValidationRules;
use App\Models\Announcement;
use Illuminate\Validation\Validator; // ✅ هذا الصحيح
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class StoreAnnouncementRequest extends FormRequest
{
    use HasAnnouncementValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Announcement::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->forceManagerAudienceIfNeeded();
    }

    public function rules(): array
    {
        return $this->commonRules(requirePresence: true);

    }

    public function messages(): array
    {
        return $this->announcementValidationMessages();
    }

    // ✅ أضيفي هالدالة
    protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }

    // ✅ أضيفي هالدالة
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
