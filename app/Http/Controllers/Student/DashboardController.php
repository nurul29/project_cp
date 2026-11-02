<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\CalculateProgress;
use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\SubjectResource;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\ReadingHistory;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, CalculateProgress $calculateProgress): Response
    {
        $student = $request->user();
        $classroomId = $student->classroom_id;

        $quizzes = Quiz::query()
            ->with(['subject:id,name,icon,tone'])
            ->withCount('questions')
            ->where(fn (Builder $query) => $query
                ->whereNull('classroom_id')
                ->orWhere('classroom_id', $classroomId))
            ->where('status', PublicationStatus::Published->value)
            ->whereDoesntHave('attempts', fn (Builder $query) => $query->where('student_id', $student->id))
            ->latest()
            ->limit(4)
            ->get();

        $featuredMaterials = Material::query()
            ->visibleToStudent($student)
            ->where('type', '!=', MaterialType::Video->value)
            ->with('subject:id,name,icon,tone')
            ->latest()
            ->limit(3)
            ->get();

        $videoCount = Material::query()
            ->visibleToStudent($student)
            ->where('type', MaterialType::Video->value)
            ->count();

        $recentReadings = $student->readingHistories()
            ->whereHas('material', fn (Builder $query) => $query
                ->visibleToStudent($student)
                ->where('type', '!=', MaterialType::Video->value))
            ->with('material.subject:id,name,slug,icon,tone')
            ->orderByDesc('last_read_at')
            ->limit(3)
            ->get();

        $allDisplayedIds = $recentReadings->pluck('material_id')
            ->merge($featuredMaterials->pluck('id'))
            ->unique();

        $savedMaterialIds = $student->shelfItems()
            ->whereIn('material_id', $allDisplayedIds)
            ->pluck('material_id');

        return Inertia::render('Student/Dashboard', [
            'subjectsCount' => Subject::count(),
            'featuredMaterials' => collect(MaterialResource::collection($featuredMaterials)->resolve($request))
                ->map(fn (array $item) => [
                    ...$item,
                    'saved' => $savedMaterialIds->contains($item['id']),
                ])
                ->values(),
            'recentReadings' => $recentReadings->map(fn (ReadingHistory $item) => [
                ...(new MaterialResource($item->material))->resolve($request),
                'lastReadAt' => $item->last_read_at->toIso8601String(),
                'saved' => $savedMaterialIds->contains($item->material_id),
            ]),
            'videoCount' => $videoCount,
            'activities' => $quizzes->map(fn (Quiz $quiz) => [
                'title' => $quiz->title,
                'type' => 'Kuis',
                'subject' => $quiz->subject?->name ?? 'Kuis',
                'icon' => $quiz->subject?->icon ?? '🧩',
                'questionCount' => $quiz->questions_count ?? 0,
                'dueAt' => null,
                'href' => route('siswa.quizzes.show', $quiz, false),
            ])->values(),
            'progress' => $calculateProgress->handle($student),
            'subjects' => SubjectResource::collection(Subject::query()
                ->orderBy('sort_order')
                ->limit(4)
                ->get(['id', 'name', 'slug', 'icon', 'description', 'tone']))->resolve($request),
        ]);
    }
}
