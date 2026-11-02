<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'title' => fake()->sentence(3),
            'status' => 'published',
            'created_by' => User::factory()->teacher(),
        ];
    }
}
