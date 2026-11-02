<?php

namespace Tests\Feature\Policies;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TeacherScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_policies_limit_teachers_to_their_subject_and_class(): void
    {
        $classroom = Classroom::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $unassignedTeacher = User::factory()->teacher()->create();
        Teaching::factory()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
        ]);
        $assignment = Assignment::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);
        $quiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);

        foreach ([$assignment, $material, $quiz] as $resource) {
            $this->assertTrue(Gate::forUser($teacher)->allows('view', $resource));
            $this->assertFalse(Gate::forUser($unassignedTeacher)->allows('view', $resource));
        }
    }
}
