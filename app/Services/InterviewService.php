<?php

namespace App\Services;

use App\Mail\CandidateInterviewScheduled;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\interview\InterviewAssignedNotification;
use App\Notifications\interview\InterviewsRankedNotification;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class InterviewService
{
    public function schedule(JobPosting $jobPosting, array $data): Interview
    {

        $interviewedBy = $data['interviewed_by'] ?? $jobPosting->requisition->requested_by;

        $exists = Interview::where('candidate_id', $data['candidate_id'])
            ->where('job_posting_id', $jobPosting->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($exists) {
            throw new \Exception('An interview has already been scheduled for this candidate.', 409);
        }

        $conflict = Interview::where('interviewed_by', $interviewedBy)
            ->where('status', 'scheduled')
            ->where(function ($query) use ($data) {
                $query->whereBetween('scheduled_at', [
                    Carbon::parse($data['scheduled_at'])->subMinutes(29),
                    Carbon::parse($data['scheduled_at'])->addMinutes(29),
                ]);
            })
            ->exists();

        if ($conflict) {
            throw new \Exception('This interviewer has another interview within 30 minutes of this time.', 422);
        }


        return DB::transaction(function () use ($jobPosting, $data, $interviewedBy) {

            $interview = Interview::create([
                'candidate_id' => $data['candidate_id'],
                'job_posting_id' => $jobPosting->id,
                'interviewed_by' => $interviewedBy,
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

    public function recordResult(Interview $interview, array $data): Interview
    {

        $interview->update([
            'rate' => $data['rate'],
            'notes' => $data['notes'] ?? null,
            'status' => 'done',
        ]);

        return $interview->fresh();
    }

    public function submitRanking(JobPosting $jobPosting, array $ranking): void
    {

        DB::transaction(function () use ($jobPosting, $ranking) {

            // ✅ 1️⃣ امسحي كل الـ ranks القديمة لهذا الإعلان
            Interview::where('job_posting_id', $jobPosting->id)
                ->where('status', 'done')
                ->update(['rank' => null]);

          // ✅ 2️⃣ تحقق من عدم تكرار الـ ranks في الطلب الجديد
        $ranks = array_column($ranking, 'rank');
        if (count($ranks) !== count(array_unique($ranks))) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Duplicate ranks are not allowed.'
            ], 422));
        }

            // ✅ 3️⃣ ضبط الـ ranks الجديدة
            foreach ($ranking as $item) {
                Interview::where('id', $item['interview_id'])
                    ->where('job_posting_id', $jobPosting->id)
                    ->update(['rank' => $item['rank']]);
            }

            // ✅ 4️⃣ إشعار HR
            $managerName = auth()->user()->full_name;
            $hrUsers = User::role('HR')->get();
            Notification::send($hrUsers, new InterviewsRankedNotification($jobPosting, $managerName));
        });
    }

    public function cancel(Interview $interview): Interview
    {
        if ($interview->status === 'done') {
            throw new \Exception('Cannot cancel a completed interview.', 422);
        }

        if ($interview->status === 'cancelled') {
            throw new \Exception('Interview is already cancelled.', 422);
        }
        $interview->update(['status' => 'cancelled']);

        return $interview->fresh();
    }
}
