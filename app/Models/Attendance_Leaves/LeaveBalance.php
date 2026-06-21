<?php

namespace App\Models\Attendance_Leaves;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LeaveBalance extends Model
{

    protected $fillable = [
    'user_id',
    'leave_type',
    'total_days',
    'used_days',
];

    public function user()
     {
        return $this->belongsTo(User::class);
     }
}
