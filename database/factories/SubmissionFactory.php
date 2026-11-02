<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'student_id' => User::factory(),
            'answer' => fake()->paragraph(),
            'file_path' => null,
            'status' => SubmissionStatus::Pending,
            'score' => null,
            'feedback' => null,
            'submitted_at' => now(),
            'graded_at' => null,
        ];
    }
}
