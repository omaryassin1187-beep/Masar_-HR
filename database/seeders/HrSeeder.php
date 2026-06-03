<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class HrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $HR = User::create([
            'full_name' => 'omarHR',
            'email' => 'ommar19455@gmail.com',
            'dep_id' => 2,
            'password' => '11111111',
            'status' => 'active',
        ]);
        $HR->assignRole('HR');
    }
}
