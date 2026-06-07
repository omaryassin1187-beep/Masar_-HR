<?php

namespace App\Services;

use App\Events\JobPostingClosed;
use App\Models\JobPosting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JobPostingService
{
    // إعلانات مغلقة ومفتوحة للhr
    public function listForHr(?string $status = null): LengthAwarePaginator
    {
        return JobPosting::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['requisition.department', 'requisition.skills', 'requisition.requestedBy'])
            ->withCount('candidates')
            ->latest()
            ->paginate(15);
    }

    // الاعلاانات المفتوحة للزوار
    public function listForPublic(): LengthAwarePaginator
    {
        return JobPosting::query()
            ->where('status', 'open')

            ->latest()
            ->paginate(15);
    }

    // تعديل بيانات الإعلان.
    public function update(JobPosting $posting, array $data): JobPosting
    {
        $posting->update($data);

        return $posting->fresh([
            'requisition.department',
            'requisition.skills',
            'requisition.requestedBy',
            'candidates',
        ]);
    }

    public function close(JobPosting $posting): JobPosting
    {
        return DB::transaction(function () use ($posting) {
            $posting->update(['status' => 'closed']);

            $posting->candidates()
                ->where('status', 'applied')
                ->update(['status' => 'rejected']);

            JobPostingClosed::dispatch($posting);

            return $posting->fresh();
        });
    }

    public function delete(JobPosting $posting): void
    {
        DB::transaction(function () use ($posting) {
            $posting->requisition->update(['status' => 'pending']);
            $posting->delete();
        });
    }
}
