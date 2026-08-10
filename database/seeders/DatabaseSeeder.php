<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            SettingSeeder::class,
            RoleSeeder::class,
            DepartmentSeeder::class,
            AdminSeeder::class,
            HrSeeder::class,
            ManagerSeeder::class,
            SkillSeeder::class,
            EmployeeSeeder::class,
            HolidaySeeder::class,
            ProfileSeeder::class,
            RecruitmentSeeder::class,
            ContractSeeder::class,
        ]);

    }
}
