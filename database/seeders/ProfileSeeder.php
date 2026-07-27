<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'user_id' => 6,
                'birth_date' => '1995-03-15',
                'hiring_date' => '2025-06-01',  // قبل سنة
                'gender' => 'male',
                'phone_number' => '0991123456',
                'address' => 'Damascus, Syria',
                'picture' => 'default.jpg',
            ],
            [
                'user_id' => 7,
                'birth_date' => '1998-07-22',
                'hiring_date' => '2026-09-2',  // شهر واحد فقط
                'gender' => 'female',
                'phone_number' => '0991234567',
                'address' => 'Aleppo, Syria',
                'picture' => 'default.jpg',
            ],
            [
                'user_id' => 8,
                'birth_date' => '1996-11-10',
                'hiring_date' => '2024-01-01',
                'gender' => 'male',
                'phone_number' => '0991345678',
                'address' => 'Homs, Syria',
                'picture' => 'default.jpg',
            ],

        ];

        foreach ($profiles as $profile) {
            Profile::updateOrCreate(
                ['user_id' => $profile['user_id']],
                $profile
            );
        }

        $this->command->info('✅ Profiles seeded successfully!');
    }
}
