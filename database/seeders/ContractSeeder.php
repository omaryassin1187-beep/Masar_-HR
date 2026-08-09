<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractData = [
            'offer_id' => 1,
            'hour_price' => 2000,
            'working_hours_per_day' => 8,
            'weekend_days' => ['friday', 'saturday'],
            'start_date' => '2025-06-01',
            'end_date' => '2030-06-01',
            'probation_period_days' => 90,
            'termination_notice_days' => 30,
            'jurisdiction' => 'Damascus, Syria',
            'signed_at' => now()->toDateString(),
            'status' => 'active',
        ];

        User::query()->each(function (User $user) use ($contractData) {

            if ($user->contracts()->exists()) {
                return;
            }

            $user->contracts()->create($contractData);
        });
    }
}
