<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Attendance_Leaves{
/**
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property string|null $check_in
 * @property string|null $check_out
 * @property string $status
 * @property int $late_minutes
 * @property int $early_leave_minutes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereEarlyLeaveMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereLateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUserId($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models\Attendance_Leaves{
/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereUpdatedAt($value)
 */
	class Holiday extends \Eloquent {}
}

namespace App\Models\Attendance_Leaves{
/**
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HourlyLeaveEquest whereUserId($value)
 */
	class HourlyLeaveEquest extends \Eloquent {}
}

namespace App\Models\Attendance_Leaves{
/**
 * @property int $id
 * @property int $user_id
 * @property string $leave_type
 * @property int|null $total_days
 * @property int $used_days
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereLeaveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereUsedDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveBalance whereUserId($value)
 */
	class LeaveBalance extends \Eloquent {}
}

namespace App\Models\Attendance_Leaves{
/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $start_date
 * @property int $days_count
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereDaysCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereUserId($value)
 */
	class LeaveRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $job_posting_id
 * @property string $full_name
 * @property string $email
 * @property int|null $experience
 * @property string|null $cv_path
 * @property string|null $cover_letter
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Interview> $interviews
 * @property-read int|null $interviews_count
 * @property-read \App\Models\JobPosting $jobPosting
 * @property-read \App\Models\Offer|null $offer
 * @property-read \App\Models\ScreeningResult|null $screeningResult
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Skill> $skills
 * @property-read int|null $skills_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereCoverLetter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereCvPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereJobPostingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Candidate whereUpdatedAt($value)
 */
	class Candidate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $candidate_id
 * @property int $skill_id
 * @property string|null $more_skill
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Candidate $candidate
 * @property-read \App\Models\Skill $skill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereCandidateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereMoreSkill($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CandidateSkill whereUpdatedAt($value)
 */
	class CandidateSkill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRequisition> $jobRequisitions
 * @property-read int|null $job_requisitions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $owner_type
 * @property int $owner_id
 * @property string $file_name
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOwnerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $candidate_id
 * @property int $job_posting_id
 * @property \Illuminate\Support\Carbon $date_interview
 * @property string $location_type
 * @property string $status
 * @property string|null $location_details
 * @property string|null $notes
 * @property string|null $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Candidate $candidate
 * @property-read \App\Models\JobPosting $jobPosting
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereCandidateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereDateInterview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereJobPostingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereLocationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereLocationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Interview whereUpdatedAt($value)
 */
	class Interview extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $job_requisition_id
 * @property string $job_title
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Candidate> $candidates
 * @property-read int|null $candidates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Interview> $interviews
 * @property-read int|null $interviews_count
 * @property-read \App\Models\JobRequisition $jobRequisition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Offer> $offers
 * @property-read int|null $offers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereJobRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobPosting whereUpdatedAt($value)
 */
	class JobPosting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $job_requisition_id
 * @property int $skill_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JobRequisition $jobRequisition
 * @property-read \App\Models\Skill $skill
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill whereJobRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill whereSkillId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequiredSkill whereUpdatedAt($value)
 */
	class JobRequiredSkill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $department_id
 * @property int $requested_by
 * @property string $job_title
 * @property string|null $description
 * @property int|null $experience
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\JobPosting|null $jobPosting
 * @property-read \App\Models\User $requestedBy
 * @property-read \App\Models\JobRequiredSkill|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Skill> $skills
 * @property-read int|null $skills_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobRequisition whereUpdatedAt($value)
 */
	class JobRequisition extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $candidate_id
 * @property int $job_posting_id
 * @property numeric $hour_price
 * @property \Illuminate\Support\Carbon $start_date
 * @property array<array-key, mixed> $weekend_days
 * @property int $working_hours_per_day
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Candidate $candidate
 * @property-read \App\Models\JobPosting $jobPosting
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereCandidateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereHourPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereJobPostingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereWeekendDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offer whereWorkingHourPerDay($value)
 */
	class Offer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $birth_date
 * @property string $hiring_date
 * @property string $gender
 * @property string $phone_number
 * @property string $address
 * @property string $picture
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $picture_url
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereHiringDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUserId($value)
 */
	class Profile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $candidate_id
 * @property int $job_posting_id
 * @property int $matched_skills_count
 * @property bool $passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Candidate $candidate
 * @property-read \App\Models\JobPosting $jobPosting
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereCandidateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereJobPostingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereMatchedSkillsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScreeningResult whereUpdatedAt($value)
 */
	class ScreeningResult extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $probation_period_days
 * @property array<array-key, mixed> $weekend_days
 * @property string|null $jurisdiction
 * @property int $termination_notice_days
 * @property string $expected_check_in
 * @property string $expected_check_out
 * @property int $sick_leave_days
 * @property int $annual_leave_days
 * @property string $currency
 * @property int $grace_period
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAnnualLeaveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereExpectedCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereExpectedCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGracePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereJurisdiction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereProbationPeriodDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSickLeaveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTerminationNoticeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWeekendDays($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Candidate> $candidates
 * @property-read int|null $candidates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobRequisition> $jobRequisitions
 * @property-read int|null $job_requisitions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Skill whereUpdatedAt($value)
 */
	class Skill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property int $dep_id
 * @property string $status
 * @property int $is_first_login
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance_Leaves\Attendance> $attendance
 * @property-read int|null $attendance_count
 * @property-read \App\Models\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance_Leaves\HourlyLeaveEquest> $hourlyLeaveRequest
 * @property-read int|null $hourly_leave_request_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance_Leaves\LeaveBalance> $leaveBalance
 * @property-read int|null $leave_balance_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance_Leaves\LeaveRequest> $leaveRequest
 * @property-read int|null $leave_request_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsFirstLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

