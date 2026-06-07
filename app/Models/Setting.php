<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'probation_period_days',
        'weekend_days',
        'jurisdiction',
        'termination_notice_days',
        'expected_check_in',
        'expected_check_out',
        'sick_leave_days',
        'annual_leave_days',
        'currency',
        'grace_period',
    ];
    protected $casts = [
        'weekend_days' => 'array',
    ];
    //تطبيق فكرة ال singleton
    protected static $instance;

    public static function instance(): static
    {
        if (!static::$instance) {
            static::$instance = static::firstOrCreate(
                ['id' => 1],
                self::defaults()
            );
        }
        
        return static::$instance;
    }
    
    public static function defaults(): array
    {
        return [
            'probation_period_days'   => 90,
            'weekend_days'            => ['friday', 'saturday'],
            'jurisdiction'            => 'Syrian Law',
            'termination_notice_days' => 30,
            'expected_check_in'       => '09:00:00',
            'expected_check_out'      => '17:00:00',
            'sick_leave_days'         => 10,
            'annual_leave_days'       => 14,
            'currency'                => 'SYP',
            'grace_period'            => 15,
        ];
    }

}
