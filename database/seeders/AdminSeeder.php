<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'full_name' => 'ceo',
            'email' => 'omar12@gmail.com',
            'dep_id' => 1,
            'password' => Hash::make('11111111'),
        ]);
        $admin->assignRole('admin');
        $admin->leaveBalance()->createMany([

            [
                'leave_type' => 'annual',
                'used_days' => 0,
                'total_days' => 30,
            ],

            [
                'leave_type' => 'sick',
                'used_days' => 0,
                'total_days' => 15
            ],

            [
                'leave_type' => 'unpaid',
                'used_days' => 0,
                'total_days' => null
            ],

        ]);
        $currency = Setting::value('currency');
        $salaryData = [
            'hour_price' => 4000,
            'currency' => $currency,
            'effective_from' => '2025-06-01',
        ];

        $admin->employeeSalaries()->create($salaryData);
        
    }
}
