<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeMarketing = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee3@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing->assignRole('employee');
        $employeeMarketing->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);

        $employeeMarketing = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee33@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing->assignRole('employee');
        $employeeMarketing->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);

        $employeeMarketing = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee36@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing->assignRole('employee');
        $employeeMarketing->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]); $employeeMarketing = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee3@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing->assignRole('employee');
        $employeeMarketing->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);

        $employeeBack = User::create([
            'full_name' => 'employeeBack',
            'email' => 'employee4@gmail.com',
            'dep_id' => 4,
            'password' => Hash::make('11111111'),
        ]);
        $employeeBack->assignRole('employee');
        $employeeBack->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);


        $employeeFront = User::create([
            'full_name' => 'employeeFront',
            'email' => 'employee5@gmail.com',
            'dep_id' => 5,
            'password' => Hash::make('11111111'),

        ]);
        $employeeFront->assignRole('employee');
        $employeeFront->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);

          $employeeFront = User::create([
            'full_name' => 'employeeFront',
            'email' => 'employee55@gmail.com',
            'dep_id' => 5,
            'password' => Hash::make('11111111'),

        ]);
        $employeeFront->assignRole('employee');
        $employeeFront->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 14,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);

        $currency = Setting::value('currency');
        $salaryData = [
            'hour_price' => 2000,
            'currency' => $currency,
            'effective_from' => '2025-06-01',
        ];

        $employeeMarketing->employeeSalaries()->create($salaryData);
        $employeeBack->employeeSalaries()->create($salaryData);
        $employeeFront->employeeSalaries()->create($salaryData);
    }
}
