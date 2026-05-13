<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class HrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $HR=User::create([
        'full_name'=>'omarHR',
        'email'=>'ommar19455@gmail.com',
        'dep_id'=>2,
        'password'=>'11111111'
        ]);
        $HR->assignRole('HR');
    }
}
