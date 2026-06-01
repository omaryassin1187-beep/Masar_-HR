<?php

namespace App\Listeners;

use App\Events\LeaveRequestSubmitted;
use App\Notifications\Leave_Requests\LeaveRequestSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class CheckLeaveRequestSender
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
    public function handle(LeaveRequestSubmitted     $event): void
    {
         $user = $event->user;
         $leaveRequest = $event->leaveRequest;

          if ($user->hasRole('employee')) {
             $manager = User::role('manager')
            ->where('dep_id', $user->dep_id)
            ->get();

           Notification::send( $manager, new LeaveRequestSubmittedNotification($leaveRequest));
        }else {
            

            $balance = $user->leaveBalance()
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
}
