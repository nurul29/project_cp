<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeachingFactory extends Factory
{
    protected $model = Teaching::class;

    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
        ];
    }
}
