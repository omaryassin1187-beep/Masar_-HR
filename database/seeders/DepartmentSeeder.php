<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $departments = ['administration', 'human Resources', 'marketing', 'backend', 'frontend'];
        foreach ($departments as $role) {
            Department::firstOrCreate(['name' => $role]);
        }
    }
}
