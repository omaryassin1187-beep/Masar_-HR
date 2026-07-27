<?php

namespace App\Models\Salary;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Deduction extends Model
{
   protected $guarded = [];

   public function user()
   {
      return $this->belongsTo(User::class);
   }

   public function scopeVisibleTo($query, User $authUser)
   {
      if ($authUser->hasRole('manager')) {
         return $query->whereHas('user', function ($q) use ($authUser) {
            $q->where('dep_id', $authUser->dep_id)
               ->whereHas('roles', function ($role) {
                  $role->where('name', 'employee');
               });
         });
      }

      if ($authUser->hasRole('HR')) {
         return $query->whereHas('user', function ($q) {
            $q->whereHas('roles', function ($role) {
               $role->whereIn('name', ['employee', 'manager']);
            });
         });
      }

      // Admin
      return $query;
   }
}
