<?php

namespace Tests\Feature\Policies;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Submission;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SubmissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_can_only_view_their_own_submission_and_assigned_teachers_can_grade_it(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $otherStudent = User::factory()->create(['classroom_id' => $classroom->id]);
        $teacher = User::factory()->teacher()->create();
        $unassignedTeacher = User::factory()->teacher()->create();
        $assignment = Assignment::factory()->create(['classroom_id' => $classroom->id]);
        Teaching::factory()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $assignment->subject_id,
            'classroom_id' => $classroom->id,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        $this->assertTrue(Gate::forUser($student)->allows('view', $submission));
        $this->assertFalse(Gate::forUser($otherStudent)->allows('view', $submission));
        $this->assertTrue(Gate::forUser($teacher)->allows('grade', $submission));
        $this->assertFalse(Gate::forUser($unassignedTeacher)->allows('grade', $submission));
    }
}
