<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobRequisition;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\Offer;

class RecruitmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Job Requisition
        $jobRequisition = JobRequisition::create([
            'department_id' => 1,
            'requested_by'  => 1,
            'job_title'     => 'Backend Laravel Developer',
            'description'   => 'We are looking for an experienced Laravel Backend Developer.',
            'experience'    => 3,
            'status'        => 'approved',
        ]);

        // Job Posting
        $jobPosting = JobPosting::create([
            'job_requisition_id' => $jobRequisition->id,
            'job_title'          => $jobRequisition->job_title,
            'description'        => $jobRequisition->description,
            'status'             => 'open',
        ]);

        // Candidate
        $candidate = Candidate::create([
            'job_posting_id' => $jobPosting->id,
            'full_name'      => 'John Doe',
            'email'          => 'john.doe@example.com',
            'experience'     => 4,
            'cv_path'        => 'cvs/john_doe.pdf',
            'cover_letter'   => 'I am interested in joining your company.',
            'more_skill'     => 'Laravel, PHP, MySQL, REST API',
            'status'         => 'offered',
        ]);

        // Offer
        Offer::create([
            'candidate_id'           => $candidate->id,
            'job_posting_id'         => $jobPosting->id,
            'hour_price'             => 20.00,
            'start_date'             => now()->addWeek()->toDateString(),
            'weekend_days'           => ['Friday', 'Saturday'],
            'working_hours_per_day'  => 8,
            'status'                 => 'pending',
        ]);
    }
}
