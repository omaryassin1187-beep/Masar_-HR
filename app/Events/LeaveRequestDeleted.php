<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\User;

class LeaveRequestDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public LeaveRequest $leaveRequest;
    /**
     * Create a new event instance.
     */
    public function __construct($user, $leaveRequest)
    {
        $this->user = $user;
        $this->leaveRequest = $leaveRequest;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
