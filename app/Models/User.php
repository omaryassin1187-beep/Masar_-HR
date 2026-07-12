<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Attendance_Leaves\Attendance;
use App\Models\Attendance_Leaves\LeaveBalance;
use App\Models\Attendance_Leaves\LeaveRequest;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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
        'is_first_login',
        'onboarding_completed_at',

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

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
