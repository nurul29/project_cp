<?php

namespace App\Filament\Teacher\Resources\QuizResource\Pages;

use App\Filament\Teacher\Resources\QuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ViewQuiz extends ViewRecord
{
    protected static string $resource = QuizResource::class;

    protected static string $view = 'filament.teacher.resources.quiz-resource.pages.view-quiz';

    public int $activeTab = 0;

    public int $previewQuestionIndex = 0;

    public string $searchStudent = '';

    public string $statusFilter = 'all';

    public ?int $selectedStudentAttemptId = null;

    public function getTitle(): string | Htmlable
    {
        /** @var Quiz $quiz */
        $quiz = $this->getRecord();
        return 'Detail Kuis: ' . $quiz->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah Kuis')
                ->icon('heroicon-o-pencil-square'),
            DeleteAction::make()
                ->label('Hapus Kuis')
                ->icon('heroicon-o-trash')
                ->successRedirectUrl(fn (): string => static::getResource()::getUrl('index')),
            Action::make('back')
                ->label('Kembali ke Daftar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }

    public function setTab(int $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setQuestionIndex(int $index): void
    {
        $this->previewQuestionIndex = $index;
    }

    public function nextQuestion(): void
    {
        /** @var Quiz $quiz */
        $quiz = $this->getRecord();
        $total = $quiz->questions()->count();
        if ($this->previewQuestionIndex < $total - 1) {
            $this->previewQuestionIndex++;
        }
    }

    public function prevQuestion(): void
    {
        if ($this->previewQuestionIndex > 0) {
            $this->previewQuestionIndex--;
        }
    }

    public function openAttemptDetail(?int $attemptId): void
    {
        $this->selectedStudentAttemptId = $attemptId;
    }

    public function closeAttemptDetail(): void
    {
        $this->selectedStudentAttemptId = null;
    }

    public function getViewData(): array
    {
        /** @var Quiz $quiz */
        $quiz = $this->getRecord();
        $quiz->loadMissing([
            'subject',
            'classroom.students' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'material',
            'questions',
            'attempts.student',
        ]);

        $classroom = $quiz->classroom;
        $allStudents = $classroom ? $classroom->students : collect();

        // Ambil attempt terbaru per siswa
        $latestAttempts = $quiz->attempts
            ->groupBy('student_id')
            ->map(fn ($group) => $group->sortByDesc('id')->first());

        $completedStudents = $allStudents->filter(fn ($s) => $latestAttempts->has($s->id));
        $pendingStudents = $allStudents->reject(fn ($s) => $latestAttempts->has($s->id));

        $totalStudentsCount = $allStudents->count();
        $completedCount = $completedStudents->count();
        $pendingCount = $pendingStudents->count();

        $scores = $latestAttempts->pluck('score')->filter(fn ($s) => $s !== null);
        $avgScore = $scores->isNotEmpty() ? round($scores->average(), 1) : 0;
        $maxScore = $scores->isNotEmpty() ? $scores->max() : 0;
        $minScore = $scores->isNotEmpty() ? $scores->min() : 0;
        $participationRate = $totalStudentsCount > 0 ? round(($completedCount / $totalStudentsCount) * 100) : 0;

        // Susun daftar siswa lengkap dengan status dan data pengerjaan
        $studentReports = $allStudents->map(function ($student) use ($latestAttempts) {
            $attempt = $latestAttempts->get($student->id);
            return [
                'id' => $student->id,
                'name' => $student->name,
                'username' => $student->username,
                'is_completed' => (bool) $attempt,
                'score' => $attempt?->score,
                'finished_at' => $attempt?->finished_at,
                'attempt_id' => $attempt?->id,
                'answers' => $attempt?->answers ?? [],
            ];
        });

        // Terapkan filter dan pencarian
        if (filled($this->searchStudent)) {
            $search = strtolower(trim($this->searchStudent));
            $studentReports = $studentReports->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search)
                    || str_contains(strtolower($item['username']), $search);
            });
        }

        if ($this->statusFilter === 'completed') {
            $studentReports = $studentReports->where('is_completed', true);
        } elseif ($this->statusFilter === 'not_completed') {
            $studentReports = $studentReports->where('is_completed', false);
        }

        // Data attempt terpilih untuk modal
        $selectedAttempt = null;
        if ($this->selectedStudentAttemptId) {
            $selectedAttempt = QuizAttempt::with('student')->find($this->selectedStudentAttemptId);
        }

        return [
            'quiz' => $quiz,
            'questions' => $quiz->questions,
            'totalStudentsCount' => $totalStudentsCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'participationRate' => $participationRate,
            'avgScore' => $avgScore,
            'maxScore' => $maxScore,
            'minScore' => $minScore,
            'studentReports' => $studentReports->values(),
            'selectedAttempt' => $selectedAttempt,
        ];
    }
}
