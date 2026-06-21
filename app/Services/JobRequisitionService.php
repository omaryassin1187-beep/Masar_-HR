<?php

namespace App\Services;

use App\Events\JobRequisitionApproved;
use App\Models\JobPosting;
use App\Models\JobRequisition;
use Illuminate\Support\Facades\DB;

class JobRequisitionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function approve(
        JobRequisition $jobRequisition,
        string $jobTitle,
        ?string $description,// اشارة الاستفهام تعني انو ممكن يكون فارغ
    ): array {
        return DB::transaction(function () use ($jobRequisition, $jobTitle, $description) {

            $jobRequisition->update(['status' => 'approved']);

            $posting = JobPosting::create([
                'job_requisition_id' => $jobRequisition->id,
                'job_title' => $jobTitle,
                'description' => $description ?? $jobRequisition->description,
                'experience' => $jobRequisition->experience,
                'status' => 'open',
            ]);

            JobRequisitionApproved::dispatch($jobRequisition, $posting);

            return [
                'requisition' => $jobRequisition->fresh(),
                'posting' => $posting,
            ];
        });
    }

    public function reject(JobRequisition $jobRequisition): JobRequisition
    {
        $jobRequisition->update([
            'status' => 'rejected',
        ]);

        return $jobRequisition->fresh()->load('skills', 'department', 'requestedBy');
    }
}
