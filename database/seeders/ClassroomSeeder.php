<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = [
            [
                'name' => 'VII A',
                'grade_level' => 'VII',
                'school_year' => '2025/2026',
            ],
            [
                'name' => 'VII B',
                'grade_level' => 'VII',
                'school_year' => '2025/2026',
            ],
            [
                'name' => 'VIII A',
                'grade_level' => 'VIII',
                'school_year' => '2025/2026',
            ],
            [
                'name' => 'VIII B',
                'grade_level' => 'VIII',
                'school_year' => '2025/2026',
            ],
            [
                'name' => 'IX A',
                'grade_level' => 'IX',
                'school_year' => '2025/2026',
            ],
            [
                'name' => 'IX B',
                'grade_level' => 'IX',
                'school_year' => '2025/2026',
            ],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::updateOrCreate(['name' => $classroom['name']], $classroom);
        }
    }
}
