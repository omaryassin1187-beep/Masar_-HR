<?php

namespace App\Models\Attendance_Leaves;

use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance_Leaves\Attendance;
class Attendance_session extends Model
{
    protected $guarded = [];
    
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
