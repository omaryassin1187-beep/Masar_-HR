<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $employeeMarketing=User::create([
        'full_name'=>'employeeMarketing',
        'email'=>'employee3@gmail.com',
        'dep_id'=>3,
        'password'=>'55555555'
        ]);
        $employeeMarketing->assignRole('employee');
        $employeeMarketing->leaveBalance()->createMany([

                    [
                        'leave_type' => 'annual',
                        'used_days' => 0,
                        'total_days'=>14,
                    ],

                    [
                        'leave_type' => 'sick',
                        'used_days' => 0,
                        'total_days'=>10
                    ],

                    [
                        'leave_type' => 'unpaid',
                        'used_days' => 0,
                        'total_days'=>null
                    ],

                ]);


         $employeeBack=User::create([
        'full_name'=>'employeeBack',
        'email'=>'employee4@gmail.com',
        'dep_id'=>4,
        'password'=>'66666666'
        ]);
        $employeeBack->assignRole('employee');
        $employeeBack->leaveBalance()->createMany([

                    [
                        'leave_type' => 'annual',
                        'used_days' => 0,
                        'total_days'=>14,
                    ],

                    [
                        'leave_type' => 'sick',
                        'used_days' => 0,
                        'total_days'=>10
                    ],

                    [
                        'leave_type' => 'unpaid',
                        'used_days' => 0,
                        'total_days'=>null
                    ],

                ]);


        $employeeFront=User::create([
        'full_name'=>'employeeFront',
        'email'=>'employee5@gmail.com',
        'dep_id'=>5,
        'password'=>'77777777'
        ]);
        $employeeFront->assignRole('employee');
         $employeeFront->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days'=>14,
            ],

            [
                'leave_type' => 'sick',
                 'used_days' => 0,
                'total_days'=>10
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days'=>null
            ],

        ]);
    }
}
