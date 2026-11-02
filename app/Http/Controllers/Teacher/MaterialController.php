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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Material::query()->with(['subject', 'classroom', 'creator']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('collection_category')) {
            if ($category !== 'all') {
                $query->where('collection_category', $category);
            }
        }

        if ($format = $request->input('content_format')) {
            if ($format !== 'all') {
                $query->where('content_format', $format);
            }
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
        } elseif ($classroomId = $request->input('classroom_id')) {
            $query->where('classroom_id', $classroomId);
        }

        $materials = $query->latest()->get()->map(function (Material $material) use ($user) {
            $isMine = $user ? (int) $material->created_by === (int) $user->id : false;
            $canDelete = $user && ($user->isAdmin() || $isMine);

            return [
                'id' => $material->id,
                'title' => $material->title,
                'collection_category' => $material->collection_category ?? 'learning',
                'content_format' => $material->content_format ?? ($material->type?->value === 'video' ? 'video' : 'pdf'),
                'author' => $material->author,
                'type' => $material->type?->value ?? $material->type,
                'genre' => $material->genre,
                'category_display' => $material->category_display,
                'target_grade' => $material->target_grade,
                'target_class_display' => $material->target_class_display,
                'file_path' => $material->file_path,
                'file_url' => $material->file_path ? Storage::url($material->file_path) : null,
                'url' => $material->url,
                'embed_url' => $material->url ? YouTube::embedUrl($material->url) : null,
                'size_bytes' => $material->size_bytes,
                'status' => $material->status?->value ?? $material->status,
                'description' => $material->description,
                'subject' => $material->subject,
                'classroom' => $material->classroom,
                'creator_name' => $material->creator?->name ?? 'Sistem',
                'is_mine' => $isMine,
                'created_at_formatted' => $material->created_at?->format('d M Y'),
                'can_delete' => $canDelete,
            ];
        });

        $allSubjects = Subject::orderBy('code')->get(['id', 'name']);
        $allClassrooms = Classroom::ordered()->get(['id', 'name']);

        $teachingSubjectIds = $user && $user->isTeacher()
            ? $user->teachings()->pluck('subject_id')->filter()->unique()
            : collect();
        $teachingSubjects = $teachingSubjectIds->isNotEmpty()
            ? Subject::whereIn('id', $teachingSubjectIds)->orderBy('code')->get(['id', 'name'])
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

        if ($teachingGrades->isEmpty()) {
            $teachingGrades = $allGrades;
        }

        return Inertia::render('Teacher/Materials/Index', [
            'pageMode' => 'materials',
            'materials' => $materials,
            'subjects' => $allSubjects,
            'teachingSubjects' => $teachingSubjects,
            'allGrades' => $allGrades,
            'teachingGrades' => $teachingGrades,
            'classrooms' => $allClassrooms,
            'genres' => array_values(Material::GENRES),
            'filters' => $request->only(['search', 'collection_category', 'content_format', 'subject_id', 'target_grade', 'classroom_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = $request->input('collection_category', 'learning');
        $user = $request->user();

        if ($category === 'general') {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'genre' => 'required|string|max:120',
                'author' => 'nullable|string|max:150',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:pdf|max:51200',
                'is_published' => 'boolean',
            ]);

            $uploadedFile = $request->file('file');
            $filePath = $uploadedFile->store('materials', 'public');
            $sizeBytes = $uploadedFile->getSize();

            Material::create([
                'title' => $validated['title'],
                'collection_category' => 'general',
                'content_format' => 'pdf',
                'type' => MaterialType::Reading,
                'genre' => $validated['genre'],
                'author' => $validated['author'] ?? null,
                'subject_id' => null,
                'classroom_id' => null,
                'target_grade' => null,
                'description' => $validated['description'] ?? '',
                'file_path' => $filePath,
                'size_bytes' => $sizeBytes,
                'cover_text' => Str::limit($validated['genre'], 28, ''),
                'status' => $request->boolean('is_published', true)
                    ? PublicationStatus::Published
                    : PublicationStatus::Draft,
                'created_by' => $user->id,
            ]);

            return back()->with('success', 'Buku bacaan umum berhasil ditambahkan.');
        }

        // Kategori Materi Pembelajaran
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_format' => 'required|in:pdf,video',
            'subject_id' => 'required|exists:subjects,id',
            'target_grade' => 'nullable|string|max:20',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'description' => 'nullable|string',
            'file' => 'required_if:content_format,pdf|nullable|file|mimes:pdf|max:51200',
            'video_url' => 'required_if:content_format,video|nullable|url',
            'is_published' => 'boolean',
        ]);

        // Cek kewenangan mengajar guru
        if ($user->isTeacher() && ! $user->isAdmin()) {
            $taughtSubjectIds = $user->teachings()->pluck('subject_id')->all();
            if (! in_array($validated['subject_id'], $taughtSubjectIds)) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Anda hanya berwenang menambahkan materi untuk mata pelajaran yang Anda ampu.',
                ]);
            }
        }

        $filePath = null;
        $sizeBytes = null;
        $url = null;
        $videoProvider = null;

        if ($validated['content_format'] === 'pdf') {
            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $filePath = $uploadedFile->store('materials', 'public');
                $sizeBytes = $uploadedFile->getSize();
            }
        } else {
            $url = $validated['video_url'];
            if (! YouTube::isEmbeddable($url)) {
                throw ValidationException::withMessages([
                    'video_url' => 'Pemilik video YouTube ini menonaktifkan pemutaran di situs lain sehingga tidak bisa diputar di SUNDIGITAL. Gunakan tautan video lain.',
                ]);
            }
            $videoProvider = VideoProvider::fromUrl($url) ?? VideoProvider::YouTube;
        }

        Material::create([
            'title' => $validated['title'],
            'collection_category' => 'learning',
            'content_format' => $validated['content_format'],
            'type' => $validated['content_format'] === 'video' ? MaterialType::Video : MaterialType::Module,
            'subject_id' => $validated['subject_id'],
            'target_grade' => ! empty($validated['target_grade']) ? $validated['target_grade'] : null,
            'classroom_id' => ! empty($validated['target_grade']) ? null : ($validated['classroom_id'] ?: null),
            'description' => $validated['description'] ?? '',
            'file_path' => $filePath,
            'size_bytes' => $sizeBytes,
            'url' => $url,
            'video_provider' => $videoProvider,
            'cover_text' => Str::limit($validated['title'], 28, ''),
            'status' => $request->boolean('is_published', true)
                ? PublicationStatus::Published
                : PublicationStatus::Draft,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Materi pembelajaran berhasil ditambahkan.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->authorize('delete', $material);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
        return back()->with('success', 'Koleksi berhasil dihapus.');
    }
}
