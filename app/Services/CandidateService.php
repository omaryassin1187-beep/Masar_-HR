<?php

namespace App\Services;

use App\Events\CandidateApplied;
use App\Models\Candidate;
use App\Models\Document;
use App\Models\JobPosting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidateService
{
    public function apply(JobPosting $posting, array $data, UploadedFile $cvFile): Candidate
    {
        $this->ensureNotDuplicate($posting->id, $data['email']); // تحقق من عدم وجود طلب سابق بنفس البريد لنفس الوظيفة

        return DB::transaction(function () use ($posting, $data, $cvFile) {

            $cvPath = $this->uploadCv($cvFile, $posting->id);

            $candidate = Candidate::create([
                'job_posting_id' => $posting->id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'cover_letter' => $data['cover_letter'] ?? null,
                'experience' => $data['experience'] ?? null,
                'status' => Candidate::STATUS_APPLIED,
            ]);

            if (! empty($data['skill_ids'])) {
                $candidate->skills()->attach($data['skill_ids']);
            }
            $candidate->update([
                'more_skill' => $data['more_skill'] ?? null,
            ]);

            Document::create([
                'owner_type' => Candidate::class,
                'owner_id' => $candidate->id,
                'file_name' => $cvFile->getClientOriginalName(), // حفظ الاسم الأصلي للملف في قاعدة البيانات
                'file_path' => $cvPath,
                'type' => 'cv',
            ]);

            event(new CandidateApplied($candidate, $posting));

            return $candidate;
        });
    }

    public function getCvForDownload(Candidate $candidate, int $expires, string $signature): array
    {
        abort_if($expires < now()->timestamp, 410, 'The link has expired.');

        $cvDoc = $candidate->documents->where('type', 'cv')->first();
        abort_if(! $cvDoc, 404, 'No CV found for this candidate.');

        $expected = hash_hmac('sha256', $candidate->id.'|'.$cvDoc->file_path, config('app.key'));
        abort_if(! hash_equals($expected, $signature), 403, 'Invalid link.');

        abort_if(! Storage::disk('private')->exists($cvDoc->file_path), 404, 'File not found.');

        return [$cvDoc->file_path, $cvDoc->file_name];
    }

    public function updateStatus(Candidate $candidate, string $status): Candidate
    {
        $allowed = [
            Candidate::STATUS_SCREENED,
            Candidate::STATUS_QUALIFIED,
            Candidate::STATUS_INTERVIEWED,
            Candidate::STATUS_OFFERED,
            Candidate::STATUS_HIRED,
            Candidate::STATUS_REJECTED,
        ];

        abort_unless(in_array($status, $allowed), 422, 'The requested status is not allowed.');

        $candidate->update(['status' => $status]);

        return $candidate;
    }

    private function ensureNotDuplicate(int $postingId, string $email): void
    {
        $exists = Candidate::where('job_posting_id', $postingId)
            ->where('email', $email)
            ->exists();

        abort_if($exists, 409, 'You have already applied to this position with the same email address.');
    }

    private function uploadCv(UploadedFile $file, int $postingId): string
    {
        $filename = time().'_'.$file->getClientOriginalName();

        return $file->storeAs("cvs/posting_{$postingId}", $filename, 'private');
    }
}
