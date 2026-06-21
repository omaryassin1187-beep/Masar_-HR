<?php

namespace App\Services;

use App\Mail\CandidateInterviewScheduled;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\InterviewAssignedNotification;
use App\Notifications\InterviewsRankedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class InterviewService
{
    public function schedule(JobPosting $jobPosting, array $data): Interview
    {
        $exists = Interview::where('candidate_id', $data['candidate_id'])
            ->where('job_posting_id', $jobPosting->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        abort_if($exists, 409, 'An interview has already been scheduled for this candidate.');

        // تحقق من عدم وجود مقابلة في نفس الوقت لنفس المدير
        $conflict = Interview::where('interviewed_by', $data['interviewed_by'])
            ->where('status', 'scheduled')
            ->where(function ($query) use ($data) {
                $query->whereBetween('scheduled_at', [
                    Carbon::parse($data['scheduled_at'])->subMinutes(29),
                    Carbon::parse($data['scheduled_at'])->addMinutes(2),
                ]);
            })
            ->exists();

        abort_if($conflict, 422, 'This interviewer has another interview within 30 minutes of this time.');

        return DB::transaction(function () use ($jobPosting, $data) {

            $interview = Interview::create([
                'candidate_id' => $data['candidate_id'],
                'job_posting_id' => $jobPosting->id,
                'interviewed_by' => $data['interviewed_by'],
                'scheduled_at' => $data['scheduled_at'],
                'location_type' => $data['location_type'],
                'location_details' => $data['location_details'],
                'status' => 'scheduled',
            ]);
             Mail::to($interview->candidate->email)
                ->send(new CandidateInterviewScheduled($interview));

            $interview->load(['candidate', 'jobPosting.requisition', 'interviewer']);
            $interview->candidate->update(['status' => 'interviewed']);
            $interview->interviewer
                ->notify(new InterviewAssignedNotification($interview));

            return $interview;
        });
    }

    //  Manager يسجّل نتيجة المقابلة
    public function recordResult(Interview $interview, array $data): Interview
    {

        $interview->update([
            'rate' => $data['rate'],
            'notes' => $data['notes'] ?? null,
            'status' => 'done',
        ]);

        return $interview->fresh();
    }

    // . Manager يرسل الترتيب النهائي لـ HR
    public function submitRanking(JobPosting $jobPosting, array $ranking): void
    {
        DB::transaction(function () use ($jobPosting, $ranking) {
            // تحديث ترتيب كل مقابلة بناءً على الترتيب المرسل
            foreach ($ranking as $item) {
                Interview::where('id', $item['interview_id'])
                    ->where('job_posting_id', $jobPosting->id)
                    ->update(['rank' => $item['rank']]);
            }

            // إشعار HR بأن الترتيب جاهز
            $hrUsers = User::role('HR')->get();
            Notification::send($hrUsers, new InterviewsRankedNotification($jobPosting));
        });
    }

    public function cancel(Interview $interview): Interview
    {
        abort_if($interview->status === 'done', 422, 'Cannot cancel a completed interview.');
        abort_if($interview->status === 'cancelled', 422, 'Interview is already cancelled.');

        $interview->update(['status' => 'cancelled']);

        return $interview->fresh();
    }
}
