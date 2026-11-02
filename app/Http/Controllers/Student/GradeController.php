<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    public function index(Request $request): Response
    {
        $attempts = QuizAttempt::query()
            ->where('student_id', $request->user()->id)
            ->whereHas('quiz')
            ->with([
                'quiz.subject:id,name,icon,tone',
                'quiz.questions:id,quiz_id,correct_index',
            ])
            ->latest('finished_at')
            ->latest('id')
            ->get();

        return Inertia::render('Student/Grades/Index', [
            'grades' => $attempts->map(function (QuizAttempt $attempt) {
                $quiz = $attempt->quiz;
                $questions = $quiz?->questions ?? collect();
                $questionCount = $questions->count();

                $correctCount = null;
                if (is_array($attempt->answers) && $questionCount > 0) {
                    $answersByQuestion = collect($attempt->answers)->keyBy('question_id');
                    $correctCount = $questions->filter(function ($question) use ($answersByQuestion) {
                        $ans = $answersByQuestion->get($question->id);
                        return is_array($ans)
                            && array_key_exists('answer_index', $ans)
                            && (int) $ans['answer_index'] === (int) $question->correct_index;
                    })->count();
                } elseif ($questionCount > 0 && $attempt->score !== null) {
                    $correctCount = (int) round(($attempt->score / 100) * $questionCount);
                }

                $summaryText = $questionCount > 0 && $correctCount !== null
                    ? "Benar {$correctCount} dari {$questionCount} soal pilihan ganda"
                    : 'Kuis pilihan ganda selesai';

                return [
                    'id' => $attempt->id,
                    'quizId' => $attempt->quiz_id,
                    'subject' => $quiz?->subject?->name ?? 'Umum',
                    'activity' => $quiz?->title ?? 'Kuis',
                    'score' => $attempt->score,
                    'correctCount' => $correctCount,
                    'questionCount' => $questionCount,
                    'feedback' => $summaryText,
                    'gradedAt' => ($attempt->finished_at ?? $attempt->created_at)?->toIso8601String(),
                    'href' => $quiz ? route('siswa.quizzes.show', $quiz, false) : null,
                ];
            })->values(),
        ]);
    }
}
