<?php

namespace App\Models\Attendance_Leaves;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance_Leaves\Attendance_session;

class Attendance extends Model
{
   protected $guarded = [];

   public function user()
   {
      return $this->belongsTo(User::class);
   }

   public function sessions()
   {
      return $this->hasMany(Attendance_session::class);
   }


   public function scopeVisibleTo($query, User $user)
   {
      if ($user->hasRole('manager')) {

         return $query->whereHas('user', function ($q) use ($user) {

            $q->where('dep_id', $user->dep_id)
               ->role('employee');
         });
      }

      return $query;
   }

   public function firstCheckIn()
   {
      return $this->sessions()
         ->orderBy('check_in')
         ->value('check_in');
   }

   public function lastCheckOut()
   {
      return $this->sessions()
         ->whereNotNull('check_out')
         ->orderByDesc('check_out')
         ->value('check_out');
   }
}
