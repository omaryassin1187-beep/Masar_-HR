<?php

namespace App\Models\Salary;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Deduction extends Model
{
    protected $guarded=[];

     public function user()
     {
        return $this->belongsTo(User::class);
     }
}
