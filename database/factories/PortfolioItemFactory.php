<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(3),
            'type' => 'worksheet',
            'file_path' => null,
            'submission_id' => null,
            'status' => 'saved',
        ];
    }
}
