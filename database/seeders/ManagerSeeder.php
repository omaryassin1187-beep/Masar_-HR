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
            'password' => '22222222',
        ]);
        $marketingManager->assignRole('manager');

        $backendManager = User::create([
            'full_name' => 'ahmadBack',
            'email' => 'omar14@gmail.com',
            'dep_id' => 4,
            'password' => '33333333',
        ]);
        $backendManager->assignRole('manager');

        $frontendManager = User::create([
            'full_name' => 'ahmadFront',
            'email' => 'omar15@gmail.com',
            'dep_id' => 5,
            'password' => '44444444',
        ]);
        $frontendManager->assignRole('manager');

    }
}
