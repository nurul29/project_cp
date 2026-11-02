<?php

namespace Database\Factories;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'title' => fake()->sentence(3),
            'instructions' => fake()->paragraph(),
            'due_at' => now()->addDays(3),
            'status' => AssignmentStatus::Active,
            'created_by' => User::factory()->teacher(),
        ];
    }
}
