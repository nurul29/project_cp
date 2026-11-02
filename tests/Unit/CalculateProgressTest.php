<?php

namespace Tests\Unit;

use App\Actions\Student\CalculateProgress;
use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_counts_active_activities_and_only_one_completion_per_quiz(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $assignment = Assignment::factory()->create(['classroom_id' => $classroom->id]);
        Assignment::factory()->create([
            'classroom_id' => $classroom->id,
            'status' => AssignmentStatus::Closed,
        ]);
        $quiz = Quiz::factory()->create(['classroom_id' => $classroom->id]);
        Quiz::factory()->create(['classroom_id' => $classroom->id, 'status' => 'closed']);
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);
        QuizAttempt::factory()->count(2)->create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);

        $this->assertSame([
            'completed' => 2,
            'total' => 2,
            'percentage' => 100,
        ], (new CalculateProgress)->handle($student));
    }

    public function test_progress_is_zero_when_the_class_has_no_active_activities(): void
    {
        $student = User::factory()->create();

        $this->assertSame([
            'completed' => 0,
            'total' => 0,
            'percentage' => 0,
        ], (new CalculateProgress)->handle($student));
    }
}
