<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class HrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $HR = User::create([
            'full_name' => 'omarHR',
            'email' => 'sososy672005@gmail.com',
            'dep_id' => 2,
            'password' => Hash::make('11111111'),
            'status' => 'active',
        ]);
        $HR->assignRole('HR');

        $HR->leaveBalance()->createMany([

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

        $HR->employeeSalaries()->create($salaryData);

        
    }
}
