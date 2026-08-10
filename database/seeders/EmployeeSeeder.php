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
            'Job_title' => 'Marketing Specialist',
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

        $employeeMarketing2 = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee33@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing2->assignRole('employee');
        $employeeMarketing2->leaveBalance()->createMany([

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

        $employeeMarketing3 = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee36@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing3->assignRole('employee');
        $employeeMarketing3->leaveBalance()->createMany([

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
        $employeeMarketing4 = User::create([
            'full_name' => 'employeeMarketing',
            'email' => 'employee113@gmail.com',
            'dep_id' => 3,
            'password' => Hash::make('11111111'),
        ]);
        $employeeMarketing4->assignRole('employee');
        $employeeMarketing4->leaveBalance()->createMany([

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
            'job_title' => 'Backend Developer'
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

        $employeeFront1 = User::create([
            'full_name' => 'employeeFront',
            'email' => 'employee55@gmail.com',
            'dep_id' => 5,
            'password' => Hash::make('11111111'),

        ]);
        $employeeFront1->assignRole('employee');
        $employeeFront1->leaveBalance()->createMany([

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
        $employeeMarketing2->employeeSalaries()->create($salaryData);
        $employeeMarketing3->employeeSalaries()->create($salaryData);
        $employeeMarketing4->employeeSalaries()->create($salaryData);
        $employeeBack->employeeSalaries()->create($salaryData);
        $employeeFront->employeeSalaries()->create($salaryData);
        $employeeFront1->employeeSalaries()->create($salaryData);

        
    }
}
