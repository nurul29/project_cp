<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\SchoolSetting;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $teaching = $user->teachings()->with(['subject', 'classroom'])->first();
        $subjectName = $teaching?->subject?->name ?? 'Matematika';
        $classrooms = $user->teachings()->with('classroom.students')->get()->pluck('classroom')->filter()->unique('id');

        $materialsQuery = Material::query()
            ->where(function ($query) use ($user, $teaching) {
                if ($teaching?->subject_id) {
                    $query->where('subject_id', $teaching->subject_id);
                } else {
                    $query->where('created_by', $user->id);
                }
            });

        $readingsCount = (clone $materialsQuery)->count();
        $publishedMaterialsCount = (clone $materialsQuery)->where('is_published', true)->count();
        $draftMaterialsCount = (clone $materialsQuery)->where('is_published', false)->count();
        $supportMaterialsCount = (clone $materialsQuery)->whereNotNull('video_url')->count();

        $recentMaterials = (clone $materialsQuery)
            ->with(['subject', 'classroom'])
            ->latest()
            ->take(6)
            ->get();

        $pendingSubmissions = Submission::query()
            ->with(['student', 'assignment.subject'])
            ->whereNull('score')
            ->latest()
            ->take(5)
            ->get();

        $activeTasks = Assignment::query()
            ->with(['subject', 'classroom', 'submissions'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'subject' => $task->subject?->name ?? 'Pelajaran',
                    'classroom' => $task->classroom?->name ?? 'Semua Kelas',
                    'due' => $task->due_at?->format('d M Y') ?? '12 Okt 2026',
                    'submissions_count' => $task->submissions->count(),
                ];
            });

        $classesSummary = $classrooms->map(function ($classroom) use ($subjectName) {
            return [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'subject' => $subjectName,
                'studentCount' => $classroom->students->count(),
            ];
        })->values();

        $settings = SchoolSetting::current();

        return Inertia::render('Teacher/Dashboard', [
            'subjectName' => $subjectName,
            'classesSummary' => $classesSummary,
            'settings' => [
                'school_name' => $settings->school_name ?? 'SMP Negeri 16 Depok',
                'school_year' => $settings->school_year ?? '2026/2027',
                'semester' => $settings->semester ?? 'Ganjil',
            ],
            'stats' => [
                'readings' => $readingsCount,
                'published' => $publishedMaterialsCount,
                'drafts' => $draftMaterialsCount,
                'support' => $supportMaterialsCount,
                'activeTasks' => $activeTasks->count(),
                'pendingSubmissions' => $pendingSubmissions->count(),
            ],
            'recentMaterials' => $recentMaterials,
            'pendingSubmissions' => $pendingSubmissions,
            'activeTasks' => $activeTasks,
        ]);
    }
}
