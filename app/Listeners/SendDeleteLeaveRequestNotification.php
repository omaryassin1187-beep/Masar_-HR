<?php

namespace App\Listeners;

use App\Events\LeaveRequestDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Leave_Requests\DeletedLeaveRequestNotification;
use Illuminate\Support\Facades\Log;

class SendDeleteLeaveRequestNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LeaveRequestDeleted $event): void
{
    Log::info('LeaveRequestDeleted listener fired', ['user_id' => $event->user->id]);

    $user = $event->user;
    $leaveRequest = $event->leaveRequest;

    if ($user->hasRole('employee')) {
        Log::info('User is employee, looking for manager');
        $manager = User::role('manager')
            ->where('dep_id', $user->dep_id)
            ->first();

        if ($manager) {
            Log::info('Manager found, sending notification', ['manager_id' => $manager->id]);
            Notification::send($manager, new DeletedLeaveRequestNotification($leaveRequest));
        } else {
            Log::warning('No manager found for dep_id: ' . $user->dep_id);
        }
    } else {
        Log::info('User is NOT employee, skipping notification');
    }
}
}
