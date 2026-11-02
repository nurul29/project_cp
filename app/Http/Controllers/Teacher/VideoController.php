<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Enums\VideoProvider;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use App\Support\YouTube;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Material::query()
            ->where('type', MaterialType::Video->value)
            ->with(['subject', 'classroom', 'creator']);

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($targetGrade = $request->input('target_grade')) {
            if ($targetGrade === 'all') {
                $query->whereNull('target_grade')->whereNull('classroom_id');
            } else {
                $query->where(function ($q) use ($targetGrade) {
                    $q->where('target_grade', $targetGrade)
                        ->orWhereHas('classroom', fn ($cq) => $cq->where('grade_level', $targetGrade));
                });
            }
        }

        $materials = $query->latest()->get()->map(function (Material $material) use ($user) {
            return [
                'id' => $material->id,
                'title' => $material->title,
                'type' => $material->type?->value ?? $material->type,
                'genre' => $material->genre,
                'category_display' => $material->category_display,
                'target_grade' => $material->target_grade,
                'target_class_display' => $material->target_class_display,
                'file_path' => $material->file_path,
                'file_url' => $material->file_path ? Storage::url($material->file_path) : null,
                'url' => $material->url,
                'size_bytes' => $material->size_bytes,
                'status' => $material->status?->value ?? $material->status,
                'description' => $material->description,
                'subject' => $material->subject,
                'classroom' => $material->classroom,
                'creator_name' => $material->creator?->name ?? 'Sistem',
                'is_mine' => $user ? (int) $material->created_by === (int) $user->id : false,
                'created_at_formatted' => $material->created_at?->format('d M Y'),
                'can_delete' => $user ? $user->can('delete', $material) : false,
            ];
        });

        $allSubjects = Subject::orderBy('name')->get(['id', 'name']);
        $teachingSubjectIds = $user && $user->isTeacher()
            ? $user->teachings()->pluck('subject_id')->filter()->unique()
            : collect();
        $teachingSubjects = $teachingSubjectIds->isNotEmpty()
            ? Subject::whereIn('id', $teachingSubjectIds)->orderBy('name')->get(['id', 'name'])
            : $allSubjects;

        $allGrades = Classroom::query()->whereNotNull('grade_level')->pluck('grade_level')->unique()->sort()->values();
        $teachingGrades = $user && $user->isTeacher()
            ? $user->teachings()
                ->with('classroom')
                ->get()
                ->pluck('classroom.grade_level')
                ->filter()
                ->unique()
                ->sort()
                ->values()
            : $allGrades;

        return Inertia::render('Teacher/Materials/Index', [
            'pageMode' => 'videos',
            'materials' => $materials,
            'subjects' => $allSubjects,
            'teachingSubjects' => $teachingSubjects,
            'allGrades' => $allGrades,
            'teachingGrades' => $teachingGrades,
            'classrooms' => Classroom::orderBy('name')->get(['id', 'name']),
            'teachingClassrooms' => Classroom::orderBy('name')->get(['id', 'name']),
            'genres' => array_values(Material::GENRES),
            'filters' => $request->only(['search', 'subject_id', 'target_grade']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'target_grade' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
            'video_url' => 'nullable|url',
            'is_published' => 'boolean',
        ]);

        if (empty($validated['video_url']) && ! $request->hasFile('file')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'video_url' => 'Harap masukkan tautan video YouTube atau unggah file video (.mp4).',
            ]);
        }

        if (! empty($validated['video_url']) && ! YouTube::isEmbeddable($validated['video_url'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'video_url' => 'Pemilik video YouTube ini menonaktifkan pemutaran di situs lain. Gunakan video lain atau unggah file .mp4.',
            ]);
        }

        $filePath = null;
        $sizeBytes = null;
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $filePath = $uploadedFile->store('materials', 'public');
            $sizeBytes = $uploadedFile->getSize();
        }

        $url = $validated['video_url'] ?? null;
        $videoProvider = $url ? (VideoProvider::fromUrl($url) ?? VideoProvider::YouTube) : null;

        Material::create([
            'title' => $validated['title'],
            'type' => MaterialType::Video,
            'genre' => null,
            'subject_id' => $validated['subject_id'],
            'target_grade' => ! empty($validated['target_grade']) ? $validated['target_grade'] : null,
            'classroom_id' => null,
            'description' => $validated['description'] ?? '',
            'file_path' => $filePath,
            'size_bytes' => $sizeBytes,
            'url' => $url,
            'video_provider' => $videoProvider,
            'cover_text' => Str::limit($validated['title'], 28, ''),
            'status' => $request->boolean('is_published', true)
                ? PublicationStatus::Published
                : PublicationStatus::Draft,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Video pembelajaran berhasil ditambahkan.');
    }
}
