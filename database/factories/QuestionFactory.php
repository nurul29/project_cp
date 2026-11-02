<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'body' => fake()->sentence() . '?',
            'options' => [
                fake()->word(),
                fake()->word(),
                fake()->word(),
                fake()->word(),
            ],
            'correct_index' => fake()->numberBetween(0, 3),
            'position' => fake()->numberBetween(1, 10),
        ];
    }
}
