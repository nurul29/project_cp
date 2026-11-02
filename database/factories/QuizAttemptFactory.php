<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'student_id' => User::factory(),
            'score' => fake()->numberBetween(60, 100),
            'total' => 100,
            'finished_at' => now(),
        ];
    }
}
