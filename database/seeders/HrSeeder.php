<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class HrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $HR=User::create([
        'full_name'=>'omarHR',
        'email'=>'ommar19455@gmail.com',
        'dep_id'=>2,
        'password'=>'11111111'
        ]);
        $HR->assignRole('HR');

                 $HR->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days'=>30,
            ],

            [
                'leave_type' => 'sick',
                 'used_days' => 0,
                'total_days'=>15
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days'=>null
            ],

        ]);
    }
}
