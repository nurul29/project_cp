<?php

namespace Tests\Feature\Student;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\PortfolioItem;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_and_update_a_pending_assignment_without_duplicate_records(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $assignment = Assignment::factory()->create(['classroom_id' => $classroom->id]);

        $this->actingAs($student)
            ->post(route('siswa.worksheets.submit', $assignment), ['answer' => 'Jawaban pertama'])
            ->assertRedirect(route('siswa.worksheets.show', $assignment))
            ->assertSessionHas('success');

        $submission = Submission::query()->sole();
        $portfolioItem = PortfolioItem::query()->sole();
        $this->assertSame(SubmissionStatus::Pending, $submission->status);
        $this->assertSame('Jawaban pertama', $submission->answer);
        $this->assertSame($submission->id, $portfolioItem->submission_id);
        $this->assertSame('worksheet', $portfolioItem->type);

        $this->post(route('siswa.worksheets.submit', $assignment), ['answer' => 'Jawaban diperbarui'])
            ->assertRedirect(route('siswa.worksheets.show', $assignment));

        $this->assertSame(1, Submission::query()->count());
        $this->assertSame(1, PortfolioItem::query()->count());
        $this->assertSame($submission->id, Submission::query()->sole()->id);
        $this->assertSame('Jawaban diperbarui', Submission::query()->sole()->answer);
    }

    public function test_student_cannot_submit_assignments_for_another_class_or_resubmit_after_grading(): void
    {
        $studentClass = Classroom::factory()->create();
        $otherClass = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $studentClass->id]);
        $assignment = Assignment::factory()->create(['classroom_id' => $otherClass->id]);
        $ownAssignment = Assignment::factory()->create(['classroom_id' => $studentClass->id]);
        Submission::factory()->create([
            'assignment_id' => $ownAssignment->id,
            'student_id' => $student->id,
            'status' => SubmissionStatus::Graded,
        ]);

        $this->actingAs($student)
            ->post(route('siswa.worksheets.submit', $assignment), ['answer' => 'Tidak berhak'])
            ->assertForbidden();

        $this->post(route('siswa.worksheets.submit', $ownAssignment), ['answer' => 'Jawaban baru'])
            ->assertForbidden();

        $this->assertSame(1, Submission::query()->count());
        $this->assertSame(0, PortfolioItem::query()->count());
    }
}
