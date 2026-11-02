<?php

namespace Tests\Feature\Student;

use App\Models\Classroom;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QuizNoAnswerLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_questions_never_include_the_correct_answer_index(): void
    {
        $classroom = Classroom::factory()->create();
        $student = User::factory()->create(['classroom_id' => $classroom->id]);
        $quiz = Quiz::factory()->create(['classroom_id' => $classroom->id]);
        Question::factory()->create([
            'quiz_id' => $quiz->id,
            'correct_index' => 2,
        ]);

        $this->actingAs($student)
            ->get(route('siswa.quizzes.show', $quiz))
            ->assertInertia(fn (Assert $page) => $page
                ->missing('quiz.questions.0.correct_index')
                ->has('quiz.questions.0.options', 4));
    }
}
