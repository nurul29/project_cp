<?php

namespace Tests\Feature\Student;

use App\Enums\MaterialType;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentLearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_the_students_recent_visible_reading(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Book,
            'status' => 'published',
        ]);
        $student->readingHistories()->create([
            'material_id' => $material->id,
            'last_read_at' => now(),
        ]);
        $student->shelfItems()->create(['material_id' => $material->id]);

        $this->actingAs($student)
            ->get(route('siswa.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('recentReadings', 1)
                ->where('recentReadings.0.id', $material->id)
                ->where('recentReadings.0.saved', true)
                ->has('recentReadings.0.lastReadAt'));
    }

    public function test_catalog_marks_materials_with_published_quizzes_and_quiz_list_distinguishes_types(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $material = Material::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'type' => MaterialType::Book,
            'status' => 'published',
        ]);
        $relatedQuiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'created_by' => $teacher->id,
            'created_at' => now()->subMinute(),
        ]);
        $independentQuiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'material_id' => null,
            'created_by' => $teacher->id,
            'created_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('siswa.catalog.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('materials.0.quizzes.0.id', $relatedQuiz->id));

        $this->get(route('siswa.quizzes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('quizzes.0.material', null)
                ->where('quizzes.1.material.title', $material->title)
                ->where('quizzes.0.subject.name', $subject->name));
    }

    public function test_quiz_review_exposes_answer_keys_and_explanations_only_after_an_attempt(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $quiz = Quiz::factory()->create(['classroom_id' => $classroom->id]);
        $question = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'correct_index' => 2,
            'explanation' => 'Gunakan rumus yang sesuai.',
        ]);

        $this->actingAs($student)
            ->get(route('siswa.quizzes.show', $quiz))
            ->assertInertia(fn (Assert $page) => $page
                ->missing('quiz.questions.0.correctIndex')
                ->missing('quiz.questions.0.explanation'));

        QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
        ]);

        $this->get(route('siswa.quizzes.show', $quiz))
            ->assertInertia(fn (Assert $page) => $page
                ->where('quiz.questions.0.id', $question->id)
                ->where('quiz.questions.0.correctIndex', 2)
                ->where('quiz.questions.0.explanation', 'Gunakan rumus yang sesuai.'));
    }
}
