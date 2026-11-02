<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'icon' => '📚',
            'description' => fake()->sentence(),
            'tone' => fake()->randomElement(['green', 'blue', 'orange', 'pink', 'lilac', 'yellow']),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
