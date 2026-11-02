<?php

namespace Tests\Feature\Student;

use App\Enums\PublicationStatus;
use App\Models\Classroom;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinishQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_answers_are_scored_on_the_server_and_retries_create_separate_attempts(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $quiz = Quiz::factory()->create(['classroom_id' => $classroom->id]);
        $firstQuestion = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'options' => ['A', 'B', 'C'],
            'correct_index' => 1,
            'position' => 1,
        ]);
        $secondQuestion = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'options' => ['D', 'E', 'F'],
            'correct_index' => 0,
            'position' => 2,
        ]);
        $this->actingAs($student);

        $this->post(route('siswa.quizzes.finish', $quiz), [
            'answers' => [
                ['question_id' => $firstQuestion->id, 'answer_index' => 1],
                ['question_id' => $secondQuestion->id, 'answer_index' => 2],
            ],
        ])->assertRedirect(route('siswa.quizzes.show', $quiz));

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'score' => 50,
            'total' => 100,
        ]);

        $this->post(route('siswa.quizzes.finish', $quiz), [
            'answers' => [
                ['question_id' => $firstQuestion->id, 'answer_index' => 1],
                ['question_id' => $secondQuestion->id, 'answer_index' => 0],
            ],
        ])->assertRedirect(route('siswa.quizzes.show', $quiz));

        $this->assertSame(2, QuizAttempt::query()->count());
        $this->get(route('siswa.quizzes.show', $quiz))
            ->assertInertia(fn ($page) => $page
                ->where('quiz.attempt.score', 100)
                ->where('quiz.attempt.total', 100));
    }

    public function test_quiz_rejects_missing_answers_invalid_options_and_questions_from_another_quiz(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $quiz = Quiz::factory()->create(['classroom_id' => $classroom->id]);
        $question = Question::factory()->create([
            'quiz_id' => $quiz->id,
            'options' => ['A', 'B'],
        ]);
        $otherQuestion = Question::factory()->create();

        $this->actingAs($student)
            ->post(route('siswa.quizzes.finish', $quiz), ['answers' => []])
            ->assertSessionHasErrors('answers');

        $this->post(route('siswa.quizzes.finish', $quiz), [
            'answers' => [['question_id' => $question->id, 'answer_index' => 2]],
        ])->assertSessionHasErrors('answers.0.answer_index');

        $this->post(route('siswa.quizzes.finish', $quiz), [
            'answers' => [['question_id' => $otherQuestion->id, 'answer_index' => 0]],
        ])->assertSessionHasErrors('answers.0.question_id');

        $this->assertSame(0, QuizAttempt::query()->count());
    }

    public function test_students_cannot_submit_a_draft_quiz(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $quiz = Quiz::factory()->create([
            'classroom_id' => $classroom->id,
            'status' => PublicationStatus::Draft,
        ]);
        $question = Question::factory()->create(['quiz_id' => $quiz->id]);

        $this->actingAs($student)
            ->post(route('siswa.quizzes.finish', $quiz), [
                'answers' => [['question_id' => $question->id, 'answer_index' => 0]],
            ])
            ->assertForbidden();

        $this->assertSame(0, QuizAttempt::query()->count());
    }
}
