<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance_Leaves\Holiday;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [

            ['name' => 'New Year Day', 'date' => '2026-01-01','type'=>'official'],
            ['name' => 'Syrian Revolution Day', 'date' => '2026-03-18','type'=>'official'],
            ['name' => 'Mother Day', 'date' => '2026-03-21','type'=>'official'],
            ['name' => 'Nowruz', 'date' => '2026-03-21','type'=>'official'],
            ['name' => 'Evacuation Day', 'date' => '2026-04-17','type'=>'official'],
            ['name' => 'Labour Day', 'date' => '2026-05-01','type'=>'official'],
            ['name' => 'Christmas Day', 'date' => '2026-12-25','type'=>'official'],
            ['name' => 'Liberation Day', 'date' => '2026-12-08','type'=>'official'],

        ];

        foreach ($holidays as $holiday) {

            Holiday::create([
                'name' => $holiday['name'],
                'date' => $holiday['date'],
                'type' => $holiday['type']
            ]);
        }
    }
}
