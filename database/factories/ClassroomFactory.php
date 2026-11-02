<?php

namespace Database\Factories;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = Classroom::class;

    public function definition(): array
    {
        return [
            'name' => 'VIII ' . fake()->randomElement(['A', 'B', 'C', 'D']),
            'grade_level' => 'VIII',
            'school_year' => '2025/2026',
        ];
    }
}
