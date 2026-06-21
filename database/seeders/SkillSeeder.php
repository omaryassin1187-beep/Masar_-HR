<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            ['name' => 'PHP'],
            ['name' => 'Laravel'],
            ['name' => 'JavaScript'],
            ['name' => 'Vue.js'],
            ['name' => 'MySQL'],
            ['name' => 'Project Management'],
            ['name' => 'Problem Solving'],
            ['name' => 'Communication Skills'],
            ['name' => 'Teamwork'],
            ['name' => 'Time Management'],
        ];
        foreach ($skills as $skill) {
            Skill::create($skill);
        }
    }
}
