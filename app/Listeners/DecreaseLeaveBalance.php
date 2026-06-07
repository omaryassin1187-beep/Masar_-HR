<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\LeaveRequestApproved;
use App\Notifications\Leave_Requests\LeaveRequestApprovedNotification;
use App\Notifications\Leave_Requests\LeaveRequestApprovedForHRNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class DecreaseLeaveBalance
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
    public function handle(LeaveRequestApproved $event): void
    {
        $leaveRequest = $event->leaveRequest;

        $LeaveRequestOwner = $leaveRequest->user; 
        Notification::send( $LeaveRequestOwner, new LeaveRequestApprovedNotification($leaveRequest));

        $hrUsers = User::role('HR')->get();
        Notification::send( $hrUsers, new LeaveRequestApprovedForHRNotification($leaveRequest));

         $balance = $LeaveRequestOwner->leaveBalance()
            ->where('leave_type', $leaveRequest['type'])
            ->first();

            if ($balance ) {
                $balance->increment(
                            'used_days',
                            $leaveRequest['days_count']
                        );
        
                    
            }
    }
}
