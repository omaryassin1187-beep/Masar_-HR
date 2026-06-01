<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\Leave_Requests\HourlyLeaveRequestApprovedNotification;
use App\Notifications\Leave_Requests\HourlyLeaveRequestApprovedForHRNotification;
use App\Events\HourlyLeaveRequestApproved;

class SendingApprovedHourlyRequestNotifications
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
    public function handle(HourlyLeaveRequestApproved $event): void
    {
        $hourlyLeaveRequest = $event->hourlyLeaveRequest;

        $LeaveRequestOwner = $hourlyLeaveRequest->user; 
        Notification::send( $LeaveRequestOwner, new HourlyLeaveRequestApprovedNotification($hourlyLeaveRequest));

        $hrUsers = User::role('HR')->get();
        Notification::send( $hrUsers, new HourlyLeaveRequestApprovedForHRNotification($hourlyLeaveRequest));
    }
}
