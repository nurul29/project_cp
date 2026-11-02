<?php

namespace App\Filament\Teacher\Widgets;

use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Filament\Teacher\Pages\GradeReport;
use App\Filament\Teacher\Resources\MaterialResource;
use App\Filament\Teacher\Resources\QuizResource;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SchoolSetting;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class TeacherOverview extends Widget
{
    protected static string $view = 'filament.teacher.widgets.teacher-overview';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $teacher = auth()->user();
        $teachings = $teacher?->teachings()->with('subject:id,name')->get() ?? collect();
        $subjectIds = $teachings->pluck('subject_id')->unique();
        $classroomIds = $teachings->pluck('classroom_id')->unique();
        $gradeLevels = Classroom::whereIn('id', $classroomIds)->pluck('grade_level')->filter()->unique();

        $subjectNames = $teachings->pluck('subject.name')->filter()->unique()->values();
        $subjectName = $subjectNames->isNotEmpty() ? $subjectNames->join(', ') : 'Guru Mata Pelajaran';
        $classrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
            ->where('is_active', true)
            ->ordered()
            ->get(['id', 'name']);

        $settings = SchoolSetting::current();

        $materialsQuery = Material::query()
            ->where(function (Builder $query) use ($teacher, $subjectIds, $classroomIds, $gradeLevels) {
                $query->where('created_by', $teacher->id)
                    ->orWhere(function (Builder $query) use ($subjectIds, $classroomIds, $gradeLevels) {
                        $query->whereIn('subject_id', $subjectIds)
                            ->where(fn (Builder $query) => $query->where(function ($sub) {
                                $sub->whereNull('classroom_id')->whereNull('target_grade');
                            })
                            ->orWhereIn('classroom_id', $classroomIds)
                            ->orWhereIn('target_grade', $gradeLevels));
                    });
            });

        $materialsCount = (clone $materialsQuery)->count();
        $readingsCount = (clone $materialsQuery)->where('type', '!=', \App\Enums\MaterialType::Video->value)->count();
        $publishedMaterialsCount = (clone $materialsQuery)->where('status', PublicationStatus::Published->value)->count();
        $draftMaterialsCount = (clone $materialsQuery)->where('status', PublicationStatus::Draft->value)->count();
        $supportMaterialsCount = (clone $materialsQuery)->where('type', \App\Enums\MaterialType::Video->value)->count();

        $materials = (clone $materialsQuery)
            ->with(['subject:id,name', 'classroom:id,name'])
            ->latest()
            ->limit(4)
            ->get();

        $quizzesQuery = Quiz::query()
            ->where(function (Builder $query) use ($teacher, $classroomIds) {
                $query->where('created_by', $teacher->id)
                    ->orWhereIn('classroom_id', $classroomIds);
            });

        $publishedQuizCount = (clone $quizzesQuery)->where('status', PublicationStatus::Published->value)->count();
        $draftQuizCount = (clone $quizzesQuery)->where('status', PublicationStatus::Draft->value)->count();

        // Active published quizzes with student attempt progress
        $publishedQuizzes = (clone $quizzesQuery)
            ->where('status', PublicationStatus::Published->value)
            ->with(['classroom:id,name', 'questions:id,quiz_id'])
            ->withCount(['attempts as completed_students_count' => function (Builder $query) {
                $query->selectRaw('count(distinct student_id)');
            }])
            ->latest()
            ->limit(3)
            ->get()
            ->map(function ($quiz) {
                $totalStudents = User::query()
                    ->where('classroom_id', $quiz->classroom_id)
                    ->where('role', UserRole::Student)
                    ->where('is_active', true)
                    ->count();

                $percent = $totalStudents > 0
                    ? (int) round(($quiz->completed_students_count / $totalStudents) * 100)
                    : 0;

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'className' => $quiz->classroom?->name ?? 'Semua kelas',
                    'questionCount' => $quiz->questions->count(),
                    'completedCount' => $quiz->completed_students_count,
                    'totalStudents' => $totalStudents,
                    'percentage' => min(100, $percent),
                    'editUrl' => QuizResource::getUrl('edit', ['record' => $quiz]),
                ];
            });

        // Recent attempts (Antrean penilaian & hasil)
        $recentAttempts = QuizAttempt::query()
            ->whereHas('quiz', function (Builder $query) use ($teacher, $classroomIds) {
                $query->where('created_by', $teacher->id)
                    ->orWhereIn('classroom_id', $classroomIds);
            })
            ->with(['quiz:id,title', 'student:id,name,username'])
            ->latest('finished_at')
            ->limit(3)
            ->get();

        $totalAttemptsCount = QuizAttempt::query()
            ->whereHas('quiz', function (Builder $query) use ($teacher, $classroomIds) {
                $query->where('created_by', $teacher->id)
                    ->orWhereIn('classroom_id', $classroomIds);
            })
            ->count();

        $activeStudentsCount = User::query()
            ->whereIn('classroom_id', $classroomIds)
            ->where('role', UserRole::Student)
            ->where('is_active', true)
            ->count();

        $classesSummary = $classrooms->map(function ($classroom) use ($teachings, $subjectName) {
            $studentCount = User::query()
                ->where('classroom_id', $classroom->id)
                ->where('role', UserRole::Student)
                ->where('is_active', true)
                ->count();

            $classSubjects = $teachings
                ->where('classroom_id', $classroom->id)
                ->pluck('subject.name')
                ->filter()
                ->unique()
                ->join(', ');

            return [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'subject' => $classSubjects !== '' ? $classSubjects : $subjectName,
                'studentCount' => $studentCount,
            ];
        });

        return [
            'teacher' => $teacher,
            'subjectName' => $subjectName,
            'subjectNames' => $subjectNames,
            'classrooms' => $classrooms,
            'settings' => $settings,
            'materialsCount' => $materialsCount,
            'readingsCount' => $readingsCount,
            'publishedMaterialsCount' => $publishedMaterialsCount,
            'draftMaterialsCount' => $draftMaterialsCount,
            'supportMaterialsCount' => $supportMaterialsCount,
            'materials' => $materials,
            'recentMaterials' => $materials,
            'publishedQuizCount' => $publishedQuizCount,
            'draftQuizCount' => $draftQuizCount,
            'publishedQuizzes' => $publishedQuizzes,
            'recentAttempts' => $recentAttempts,
            'totalAttemptsCount' => $totalAttemptsCount,
            'activeStudentsCount' => $activeStudentsCount,
            'classesSummary' => $classesSummary,
            'materialsUrl' => MaterialResource::getUrl(),
            'createMaterialUrl' => MaterialResource::getUrl('create'),
            'quizzesUrl' => QuizResource::getUrl(),
            'createQuizUrl' => QuizResource::getUrl('create'),
            'gradeReportUrl' => GradeReport::getUrl(),
        ];
    }
}
