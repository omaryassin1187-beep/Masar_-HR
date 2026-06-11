<?php

namespace App\Models\Attendance_Leaves;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Attendance extends Model
{
   protected $guarded = [];

   public function user()
   {
      return $this->belongsTo(User::class);
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
}
