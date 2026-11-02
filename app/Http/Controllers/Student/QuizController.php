<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\FinishQuiz;
use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\FinishQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::query()
            ->where(fn ($query) => $query
                ->whereNull('classroom_id')
                ->orWhere('classroom_id', $request->user()->classroom_id))
            ->where('status', PublicationStatus::Published->value)
            ->withCount('questions')
            ->with([
                'subject:id,name,icon,tone',
                'material' => fn ($query) => $query
                    ->visibleToStudent($request->user())
                    ->where('type', '!=', MaterialType::Video->value)
                    ->select(['id', 'title']),
                'attempts' => fn ($query) => $query
                ->where('student_id', $request->user()->id)
                ->latest('id'),
            ])
            ->latest()
            ->get();

        return Inertia::render('Student/Quizzes/Index', [
            'quizzes' => QuizResource::collection($quizzes)->resolve($request),
        ]);
    }

    public function show(Request $request, Quiz $quiz): Response
    {
        abort_unless($request->user()->can('view', $quiz), 404);

        $quiz->load([
            'subject:id,name,icon,tone',
            'material' => fn ($query) => $query
                ->visibleToStudent($request->user())
                ->where('type', '!=', MaterialType::Video->value)
                ->select(['id', 'title']),
            'questions:id,quiz_id,body,options,correct_index,explanation,position',
            'attempts' => fn ($query) => $query
                ->where('student_id', $request->user()->id)
                ->latest('id'),
        ]);

        return Inertia::render('Student/Quizzes/Show', [
            'quiz' => (new QuizResource($quiz))->resolve($request),
        ]);
    }

    public function finish(
        FinishQuizRequest $request,
        Quiz $quiz,
        FinishQuiz $finishQuiz,
    ): RedirectResponse {
        $finishQuiz->handle($quiz, $request->user(), $request->validated('answers'));

        return to_route('siswa.quizzes.show', $quiz)
            ->with('success', 'Kuis berhasil diselesaikan.');
    }
}
