<?php

namespace App\Http\Controllers\Student;

use App\Enums\MaterialType;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Models\ReadingHistory;
use App\Models\ShelfItem;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Material::class);

        $student = $request->user();
        $category = $request->query('category', 'all');
        $subject = $request->query('subject');
        $genre = $request->query('genre');
        $search = trim((string) $request->query('q', ''));

        $baseQuery = Material::query()
            ->visibleToStudent($student)
            ->where('type', '!=', MaterialType::Video->value);

        // Hitung total koleksi per kategori untuk indikator tab
        $totalLearningCount = (clone $baseQuery)->where(function (Builder $q) {
            $q->where('collection_category', 'learning')
              ->orWhere(function (Builder $sub) {
                  $sub->whereNull('collection_category')->where('type', '!=', MaterialType::Reading->value);
              });
        })->count();

        $totalGeneralCount = (clone $baseQuery)->where(function (Builder $q) {
            $q->where('collection_category', 'general')
              ->orWhere('type', MaterialType::Reading->value);
        })->count();

        $totalAllCount = $totalLearningCount + $totalGeneralCount;

        // Terapkan filter kategori
        $query = clone $baseQuery;
        if ($category === 'learning') {
            $query->where(function (Builder $q) {
                $q->where('collection_category', 'learning')
                  ->orWhere(function (Builder $sub) {
                      $sub->whereNull('collection_category')->where('type', '!=', MaterialType::Reading->value);
                  });
            });
        } elseif ($category === 'general') {
            $query->where(function (Builder $q) {
                $q->where('collection_category', 'general')
                  ->orWhere('type', MaterialType::Reading->value);
            });
        }

        if ($subject) {
            $query->whereHas('subject', fn (Builder $q) => $q->where('slug', $subject));
        }

        if ($genre) {
            $query->where('genre', $genre);
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $materials = $query->with([
                'subject:id,name,slug,icon,tone',
                'quizzes' => fn ($query) => $query
                    ->where('classroom_id', $student->classroom_id)
                    ->where('status', \App\Enums\PublicationStatus::Published->value)
                    ->select(['id', 'material_id', 'title']),
            ])
            ->latest()
            ->get();

        $genres = array_values(Material::GENRES);

        return Inertia::render('Student/Library/Index', [
            'materials' => MaterialResource::collection($materials)->resolve($request),
            'subjects' => Subject::query()->orderBy('code')->get(['id', 'name', 'slug']),
            'genres' => $genres,
            'selectedCategory' => $category,
            'selectedSubject' => $subject,
            'selectedGenre' => $genre,
            'search' => $search,
            'counts' => [
                'all' => $totalAllCount,
                'learning' => $totalLearningCount,
                'general' => $totalGeneralCount,
            ],
            'savedMaterialIds' => $student->shelfItems()->pluck('material_id'),
        ]);
    }

    public function shelf(Request $request): Response
    {
        $student = $request->user();
        $items = $student->shelfItems()
            ->whereHas('material', fn (Builder $query) => $query
                ->visibleToStudent($student)
                ->where('type', '!=', MaterialType::Video->value))
            ->with('material.subject:id,name,slug,icon,tone')
            ->latest()
            ->get()
            ->map(fn (ShelfItem $item) => [
                ...(new MaterialResource($item->material))->resolve($request),
                'savedAt' => $item->created_at->toIso8601String(),
            ]);

        return Inertia::render('Student/Library/Shelf', [
            'materials' => $items,
        ]);
    }

    public function history(Request $request): Response
    {
        $student = $request->user();
        $savedMaterialIds = $student->shelfItems()->pluck('material_id');
        $items = $student->readingHistories()
            ->whereHas('material', fn (Builder $query) => $query
                ->visibleToStudent($student)
                ->where('type', '!=', MaterialType::Video->value))
            ->with('material.subject:id,name,slug,icon,tone')
            ->orderByDesc('last_read_at')
            ->get()
            ->map(fn (ReadingHistory $item) => [
                ...(new MaterialResource($item->material))->resolve($request),
                'lastReadAt' => $item->last_read_at->toIso8601String(),
                'saved' => $savedMaterialIds->contains($item->material_id),
            ]);

        return Inertia::render('Student/Library/History', [
            'materials' => $items,
        ]);
    }

    public function toggleSaved(Request $request, Material $material): RedirectResponse
    {
        abort_unless($request->user()->can('view', $material), 404);
        abort_if($material->type === MaterialType::Video, 404);

        $shelfItem = $request->user()->shelfItems()
            ->where('material_id', $material->id)
            ->first();

        if ($shelfItem) {
            $shelfItem->delete();
            $message = 'Buku dihapus dari rak.';
        } else {
            $request->user()->shelfItems()->create([
                'material_id' => $material->id,
            ]);
            $message = 'Buku ditambahkan ke rak.';
        }

        return back()->with('success', $message);
    }

    public function show(Request $request, Material $material): Response
    {
        abort_unless($request->user()->can('view', $material), 404);
        $student = $request->user();

        if ($material->type !== MaterialType::Video) {
            $student->readingHistories()->updateOrCreate(
                ['material_id' => $material->id],
                ['last_read_at' => now()],
            );
        }

        $material->load([
            'subject:id,name,slug,icon,tone',
            'quizzes' => fn ($query) => $query
                ->where('classroom_id', $student->classroom_id)
                ->where('status', \App\Enums\PublicationStatus::Published->value)
                ->select(['id', 'material_id', 'title']),
        ]);

        return Inertia::render('Student/Library/Show', [
            'material' => (new MaterialResource($material))->resolve($request),
        ]);
    }
}
