<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Material::query()
            ->whereIn('type', [MaterialType::Book->value, MaterialType::Reading->value])
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
        $allGrades = Classroom::query()->whereNotNull('grade_level')->pluck('grade_level')->unique()->sort()->values();

        return Inertia::render('Teacher/Materials/Index', [
            'pageMode' => 'library',
            'materials' => $materials,
            'subjects' => $allSubjects,
            'teachingSubjects' => $allSubjects,
            'allGrades' => $allGrades,
            'teachingGrades' => $allGrades,
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
            'type' => 'required|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'genre' => 'nullable|string|max:120',
            'target_grade' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf|max:51200',
            'is_published' => 'boolean',
        ]);

        $materialType = $validated['type'] === 'Bacaan' ? MaterialType::Reading : MaterialType::Book;
        $subjectId = $validated['subject_id'] ?? null;

        if ($materialType === MaterialType::Reading && empty($subjectId)) {
            $subjectId = Subject::where('slug', 'bahasa-indonesia')->value('id') ?? Subject::query()->value('id');
        }

        $uploadedFile = $request->file('file');
        $filePath = $uploadedFile ? $uploadedFile->store('materials', 'public') : null;
        $sizeBytes = $uploadedFile ? $uploadedFile->getSize() : null;

        Material::create([
            'title' => $validated['title'],
            'type' => $materialType,
            'genre' => $materialType === MaterialType::Reading ? ($validated['genre'] ?? null) : null,
            'subject_id' => $subjectId,
            'target_grade' => ! empty($validated['target_grade']) ? $validated['target_grade'] : null,
            'classroom_id' => null,
            'description' => $validated['description'] ?? '',
            'file_path' => $filePath,
            'size_bytes' => $sizeBytes,
            'cover_text' => $materialType === MaterialType::Reading && ! empty($validated['genre'])
                ? Str::limit($validated['genre'], 28, '')
                : Str::limit($validated['title'], 28, ''),
            'status' => $request->boolean('is_published', true)
                ? PublicationStatus::Published
                : PublicationStatus::Draft,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Buku/bacaan berhasil ditambahkan ke perpustakaan.');
    }
}
