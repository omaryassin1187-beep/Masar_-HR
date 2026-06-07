<?php

namespace App\Models\Attendance_Leaves;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Attendance extends Model
{
   protected $guarded=[];

     public function user()
     {
        return $this->belongsTo(User::class);
     } 
}
