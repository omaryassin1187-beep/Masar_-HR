<?php

namespace Database\Seeders;

use App\Models\User;
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
            'password' => '00000000',
        ]);
        $admin->assignRole('admin');
    }
}
