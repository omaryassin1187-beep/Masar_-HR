<?php

namespace App\Models\Salary;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
   protected $guarded = [];

   public function user()
   {
      return $this->belongsTo(User::class);
   }
}
