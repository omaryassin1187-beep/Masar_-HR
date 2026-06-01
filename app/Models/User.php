<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Attendance_Leaves\LeaveBalance;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles,HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'dep_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
       return $this->hasOne(Profile::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function leaveBalance()
    {
       return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequest()
    {
       return $this->hasMany(LeaveRequest::class);
    }

    public function hourlyLeaveRequest()
    {
        return $this->hasMany(HourlyLeaveEquest::class);
    }

     
}
