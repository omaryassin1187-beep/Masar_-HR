<?php

namespace App\Http\Requests\Announcements\Concerns;

use App\Models\Announcement;
use Illuminate\Validation\Rule;

/**
 * منطق مشترك بين StoreAnnouncementRequest و UpdateAnnouncementRequest
 */
trait HasAnnouncementValidationRules
{
    protected function commonRules(bool $requirePresence): array
    {
        $presence = $requirePresence ? 'required' : 'sometimes';

        return [
            'title' => [$presence, 'string', 'max:200'],
            'content' => [$presence, 'string'],
            'priority' => [$presence, Rule::in([
                Announcement::PRIORITY_LOW,
                Announcement::PRIORITY_MEDIUM,
                Announcement::PRIORITY_HIGH,
            ])],
            'target_audience' => [$presence, Rule::in([
                Announcement::AUDIENCE_ALL,
                Announcement::AUDIENCE_DEPARTMENT,
                Announcement::AUDIENCE_MANAGERS,
            ])],

            'department_id' => [
                'nullable',
                'integer',
                'exists:departments,id',
                Rule::requiredIf(fn() => $this->input('target_audience') === Announcement::AUDIENCE_DEPARTMENT),
                Rule::prohibitedIf(fn() => $this->input('target_audience') !== Announcement::AUDIENCE_DEPARTMENT),
            ],
            'starts_at' => [$presence, 'date', 'after:now'],
            'expires_at' => [$presence, 'date',  'after:starts_at'],
        ];
    }


    protected function forceManagerAudienceIfNeeded(): void
    {
        $user = $this->user();

        if ($user && $user->hasRole('manager')) {
            $this->merge([
                'target_audience' => Announcement::AUDIENCE_DEPARTMENT,
                'department_id' => $user->dep_id,
            ]);
        }
    }

    protected function announcementValidationMessages(): array
    {

        return [

            'starts_at.after' => 'Start date and time must be in the future.',
            'expires_at.after' => 'The expiration date must be after the start date.',
            'target_audience.required' => 'The target audience must be specified.',
            'department_id.required_if' => 'Department must be specified when targeting a specific department.',
            'department_id.prohibited_if' => 'Department should not be specified unless targeting a specific department.',
        ];
    }
}
