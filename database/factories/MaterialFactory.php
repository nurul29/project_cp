<?php

namespace Database\Factories;

use App\Enums\MaterialType;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'title' => fake()->sentence(4),
            'type' => fake()->randomElement(MaterialType::cases()),
            'file_path' => null,
            'url' => fake()->url(),
            'cover_text' => 'Modul',
            'tone' => 'green',
            'description' => fake()->paragraph(),
            'created_by' => User::factory()->teacher(),
        ];
    }
}
