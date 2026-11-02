<?php

namespace App\Filament\Teacher\Pages;

use App\Enums\PublicationStatus;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class GradeReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Rekap Nilai Siswa';

    protected static ?string $title = 'Rekap Nilai Siswa';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.teacher.pages.grade-report';

    public string $mode = 'summary'; // 'summary' or 'detail'

    public ?int $selectedClassroomId = null;

    public ?int $selectedQuizId = null;

    public string $search = '';

    public string $statusFilter = 'all';

    public ?int $viewingAttemptId = null;

    public function mount(): void
    {
        $classrooms = $this->getClassrooms();
        if ($classrooms->isNotEmpty()) {
            $this->selectedClassroomId = $classrooms->first()->id;
        }

        $quizzes = $this->getQuizzes();
        if ($quizzes->isNotEmpty()) {
            $this->selectedQuizId = $quizzes->first()->id;
        }
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function updatedSelectedClassroomId(): void
    {
        $quizzes = $this->getQuizzes();
        if ($quizzes->isNotEmpty()) {
            $this->selectedQuizId = $quizzes->first()->id;
        } else {
            $this->selectedQuizId = null;
        }
    }

    public function getClassrooms(): Collection
    {
        $user = auth()->user();
        if ($user && $user->isTeacher()) {
            $classroomIds = $user->teachings()->pluck('classroom_id')->unique();

            return Classroom::whereIn('id', $classroomIds)->get();
        }

        return Classroom::all();
    }

    public function getQuizzes(): Collection
    {
        $query = Quiz::where('status', PublicationStatus::Published->value);

        if ($this->selectedClassroomId) {
            $query->where('classroom_id', $this->selectedClassroomId);
        }

        $user = auth()->user();
        if ($user && $user->isTeacher()) {
            $subjectIds = $user->teachings()->pluck('subject_id')->unique();
            $query->whereIn('subject_id', $subjectIds);
        }

        return $query->with('subject')->latest()->get();
    }

    public function getSummaryData(): array
    {
        if (! $this->selectedClassroomId) {
            return ['rows' => [], 'metrics' => ['total_students' => 0, 'total_quizzes' => 0, 'complete_count' => 0, 'pending_count' => 0]];
        }

        $quizzes = $this->getQuizzes();
        $quizCount = $quizzes->count();
        $quizIds = $quizzes->pluck('id');

        $students = User::where('role', 'student')
            ->where('classroom_id', $this->selectedClassroomId)
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', "%{$this->search}%")->orWhere('username', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->get();

        $rows = [];
        $allCompleteCount = 0;

        foreach ($students as $student) {
            $attempts = QuizAttempt::whereIn('quiz_id', $quizIds)
                ->where('student_id', $student->id)
                ->get();

            $completedQuizIds = $attempts->pluck('quiz_id')->unique();
            $completedCount = $completedQuizIds->count();

            $bestScores = [];
            foreach ($completedQuizIds as $qId) {
                $bestScores[] = $attempts->where('quiz_id', $qId)->max('score');
            }

            $averageScore = count($bestScores) > 0 ? round(array_sum($bestScores) / count($bestScores), 1) : null;
            $isComplete = ($quizCount > 0 && $completedCount >= $quizCount);

            if ($isComplete) {
                $allCompleteCount++;
            }

            $matchesStatus = match ($this->statusFilter) {
                'done' => $isComplete,
                'pending' => ! $isComplete,
                default => true,
            };

            if ($matchesStatus) {
                $rows[] = [
                    'student' => $student,
                    'completed_count' => $completedCount,
                    'total_quizzes' => $quizCount,
                    'average_score' => $averageScore,
                    'is_complete' => $isComplete,
                ];
            }
        }

        return [
            'rows' => $rows,
            'metrics' => [
                'total_students' => $students->count(),
                'total_quizzes' => $quizCount,
                'complete_count' => $allCompleteCount,
                'pending_count' => max(0, $students->count() - $allCompleteCount),
            ],
        ];
    }

    public function getQuizDetailData(): array
    {
        $quiz = Quiz::with(['classroom', 'subject', 'questions'])->find($this->selectedQuizId);
        if (! $quiz) {
            return ['quiz' => null, 'rows' => [], 'metrics' => ['total_students' => 0, 'done_count' => 0, 'pending_count' => 0, 'average_score' => 0, 'percent_done' => 0]];
        }

        $students = User::where('role', 'student')
            ->where('classroom_id', $quiz->classroom_id)
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub->where('name', 'like', "%{$this->search}%")->orWhere('username', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->get();

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get();

        $rows = [];
        $doneCount = 0;

        foreach ($students as $student) {
            $studentAttempts = $attempts->where('student_id', $student->id)->sortByDesc('finished_at');
            $hasAttempt = $studentAttempts->isNotEmpty();
            $bestScore = $hasAttempt ? $studentAttempts->max('score') : null;
            $latestAttempt = $studentAttempts->first();

            if ($hasAttempt) {
                $doneCount++;
            }

            $matchesStatus = match ($this->statusFilter) {
                'done' => $hasAttempt,
                'pending' => ! $hasAttempt,
                default => true,
            };

            if ($matchesStatus) {
                $rows[] = [
                    'student' => $student,
                    'status' => $hasAttempt ? 'done' : 'not_started',
                    'best_score' => $bestScore,
                    'latest_score' => $latestAttempt?->score,
                    'attempts_count' => $studentAttempts->count(),
                    'latest_date' => $latestAttempt?->finished_at?->format('d M Y, H:i') ?? '—',
                    'latest_attempt_id' => $latestAttempt?->id,
                ];
            }
        }

        $avgBest = $doneCount > 0 ? round(collect($rows)->where('best_score', '!==', null)->avg('best_score'), 1) : 0;

        return [
            'quiz' => $quiz,
            'rows' => $rows,
            'metrics' => [
                'total_students' => $students->count(),
                'done_count' => $doneCount,
                'pending_count' => $students->count() - $doneCount,
                'average_score' => $avgBest,
                'percent_done' => $students->count() > 0 ? round(($doneCount / $students->count()) * 100) : 0,
            ],
        ];
    }

    public function viewAttempt(int $attemptId): void
    {
        $this->viewingAttemptId = $attemptId;
    }

    public function closeAttemptModal(): void
    {
        $this->viewingAttemptId = null;
    }

    public function getViewingAttempt(): ?QuizAttempt
    {
        if (! $this->viewingAttemptId) {
            return null;
        }

        return QuizAttempt::with(['quiz.questions', 'student'])->find($this->viewingAttemptId);
    }
}
