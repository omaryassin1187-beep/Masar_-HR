<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\Complaints\ComplaintStatusUpdatedNotification;
use App\Notifications\Complaints\NewComplaintNotification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ComplaintService
{
    public function create(array $data, User $author): Complaint
    {
        $subject = User::query()
            ->select(['id', 'dep_id'])
            ->with('roles')
            ->findOrFail($data['subject_id']);

        $routeType = $subject->hasRole('manager')
            ? Complaint::ROUTE_AGAINST_MANAGER
            : Complaint::ROUTE_AGAINST_EMPLOYEE;

        $complaint = DB::transaction(function () use ($data, $author, $routeType) {
            return Complaint::create([
                'author_id' => $author->id,
                'subject_id' => $data['subject_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'route_type' => $routeType,
                'status' => Complaint::STATUS_PENDING,
            ]);
        });

        $this->safeNotify(
            fn () => Notification::send(User::role('HR')->get(), new NewComplaintNotification($complaint)),
            $complaint,
            'HR notification for new complaint'
        );

        return $complaint;
    }

    public function markUnderReview(Complaint $complaint, User $actor): Complaint
    {
        if ($complaint->status !== Complaint::STATUS_PENDING) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Complaint can only be moved to "under review" if it is currently "pending".',
                ], 422)
            );
        }

        DB::transaction(function () use ($complaint) {
            $complaint->update(['status' => Complaint::STATUS_UNDER_REVIEW]);
        });

        $this->safeNotify(
            fn () => $complaint->author->notify(new ComplaintStatusUpdatedNotification($complaint)),
            $complaint,
            'Status update notification to complainant'
        );

        return $complaint->refresh();
    }

    public function respond(Complaint $complaint, array $data, User $actor): Complaint
    {
        if ($complaint->isResolved()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'This complaint is already resolved and cannot be responded to again.',
                ], 422)
            );
        }

        DB::transaction(function () use ($complaint, $data, $actor) {
            $complaint->update([
                'hr_note' => $data['hr_note'],
                'status' => $data['status'],
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
            ]);
        });

        $this->safeNotify(
            fn () => $complaint->author->notify(new ComplaintStatusUpdatedNotification($complaint)),
            $complaint,
            'HR response notification to complainant'
        );

        return $complaint->refresh();
    }

    private function safeNotify(callable $send, Complaint $complaint, string $context): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            Log::error("Failed to send notification: {$context}", [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
