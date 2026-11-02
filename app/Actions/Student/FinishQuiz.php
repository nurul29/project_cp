<?php

namespace App\Actions\Student;

use App\Enums\PublicationStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ValidationException;

class FinishQuiz
{
    /**
     * @param  array<int, array{question_id: int, answer_index: int}>  $answers
     */
    public function handle(Quiz $quiz, User $student, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($quiz, $student, $answers): QuizAttempt {
            $quiz = Quiz::query()
                ->lockForUpdate()
                ->findOrFail($quiz->id);

            if ($quiz->status !== PublicationStatus::Published) {
                throw ValidationException::withMessages([
                    'answers' => 'Kuis ini sedang tidak aktif.',
                ]);
            }

            $questions = $quiz->questions()
                ->get(['id', 'options', 'correct_index']);

            if ($questions->isEmpty() || count($answers) !== $questions->count()) {
                throw ValidationException::withMessages([
                    'answers' => 'Jawaban harus mencakup semua soal kuis.',
                ]);
            }

            $answersByQuestion = collect($answers)->keyBy('question_id');
            $correctAnswers = 0;

            foreach ($questions as $question) {
                $answer = $answersByQuestion->get($question->id);

                if (
                    ! $answer
                    || ! array_key_exists('answer_index', $answer)
                    || $answer['answer_index'] < 0
                    || $answer['answer_index'] >= count($question->options)
                ) {
                    throw ValidationException::withMessages([
                        'answers' => 'Satu atau beberapa jawaban tidak valid.',
                    ]);
                }

                if ((int) $answer['answer_index'] === $question->correct_index) {
                    $correctAnswers++;
                }
            }

            return QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'revision' => $quiz->revision ?? 1,
                'answers' => array_values($answers),
                'score' => (int) round($correctAnswers / $questions->count() * 100),
                'total' => 100,
                'finished_at' => now(),
            ]);
        });
    }
}
