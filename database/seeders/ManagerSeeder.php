<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marketingManager = User::create([
            'full_name' => 'ahmadMarketing',
            'email' => 'omar13@gmail.com',
            'dep_id' => 3,
            'password' => '11111111',
        ]);
        $marketingManager->assignRole('manager');
         $marketingManager->leaveBalance()->createMany([

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


        $backendManager = User::create([
            'full_name' => 'ahmadBack',
            'email' => 'omar14@gmail.com',
            'dep_id' => 4,
            'password' => '11111111',
        ]);
        $backendManager->assignRole('manager');
        $backendManager->leaveBalance()->createMany([

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


        $frontendManager = User::create([
            'full_name' => 'ahmadFront',
            'email' => 'omar15@gmail.com',
            'dep_id' => 5,
            'password' => '11111111',
        ]);
        $frontendManager->assignRole('manager');
        $frontendManager->leaveBalance()->createMany([

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
