<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
     protected $guarded = [];

     public function user()
     {
        return $this->belongsTo(User::class);
     }

      public function getPictureUrlAttribute()
   {
      return $this->picture
         ? asset(Storage::url($this->picture))
         : null;
   }
}
