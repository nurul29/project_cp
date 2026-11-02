<?php

namespace App\Filament\Teacher\Resources\MaterialResource\Pages;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Filament\Teacher\Resources\MaterialResource;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ListMaterials extends ListRecords
{
    protected static string $resource = MaterialResource::class;

    protected static string $view = 'filament.teacher.pages.list-materials';

    public string $selectedCategory = 'all';

    public string $searchQuery = '';

    public ?int $subjectFilter = null;

    public ?string $genreFilter = null;

    public ?string $gradeFilter = null;

    public ?int $previewMaterialId = null;

    public function getTitle(): string
    {
        return 'Katalog perpustakaan & koleksi digital';
    }

    public function getSubheading(): ?string
    {
        if (auth()->user()?->isAdmin()) {
            return 'Jelajahi dan kelola seluruh buku pelajaran, modul, bacaan umum, dan video pembelajaran di perpustakaan sekolah.';
        }

        return 'Jelajahi buku pelajaran, modul, bacaan umum, dan video pembelajaran lintas mata pelajaran, atau kelola koleksi yang Anda publikasikan.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah koleksi'),
        ];
    }

    public function setCategory(string $category): void
    {
        $this->selectedCategory = $category;

        if ($category === 'general') {
            $this->subjectFilter = null;
            $this->gradeFilter = null;
        } elseif ($category === 'learning' || $category === 'video') {
            $this->genreFilter = null;
        }
    }

    public function resetCardFilters(): void
    {
        $this->searchQuery = '';
        $this->subjectFilter = null;
        $this->genreFilter = null;
        $this->gradeFilter = null;
    }

    public function openPreview(int $id): void
    {
        $this->previewMaterialId = $id;
    }

    public function closePreview(): void
    {
        $this->previewMaterialId = null;
    }

    public function deleteMaterial(int $id): void
    {
        $material = Material::find($id);
        if (! $material) {
            return;
        }

        $user = auth()->user();
        $canDelete = $user && ($user->isAdmin() || (int) $material->created_by === (int) $user->id || $user->can('delete', $material));

        if (! $canDelete) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Anda hanya dapat menghapus koleksi yang Anda unggah sendiri.')
                ->danger()
                ->send();

            return;
        }

        try {
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }

            $material->delete();

            if ($this->previewMaterialId === $id) {
                $this->previewMaterialId = null;
            }

            Notification::make()
                ->title('Koleksi berhasil dihapus')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Tidak dapat menghapus koleksi')
                ->body(collect($e->errors())->flatten()->first() ?? 'Koleksi masih digunakan pada kuis aktif.')
                ->danger()
                ->send();
        }
    }

    protected function getBaseVisibilityQuery(): Builder
    {
        $user = auth()->user();
        $query = Material::query();

        // Admin melihat seluruh koleksi (Terbit maupun Draf dari semua guru/admin)
        if ($user && $user->isAdmin()) {
            return $query;
        }

        // Guru melihat seluruh koleksi yang Terbit (lintas mata pelajaran & kelas) + seluruh koleksi miliknya sendiri (termasuk Draf)
        $userId = (int) ($user?->id ?? 0);

        return $query->where(function (Builder $q) use ($userId) {
            $q->where('status', PublicationStatus::Published->value)
                ->orWhere('created_by', $userId);
        });
    }

    protected static function applyCategoryCondition(Builder $query, string $category, int $userId): Builder
    {
        return match ($category) {
            'learning' => $query->where(function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('collection_category', 'learning')
                        ->orWhere(function (Builder $sub2) {
                            $sub2->whereNull('collection_category')
                                ->where('type', '!=', MaterialType::Reading->value);
                        });
                })->where('type', '!=', MaterialType::Video->value)
                  ->where(fn (Builder $f) => $f->whereNull('content_format')->orWhere('content_format', '!=', 'video'));
            }),
            'general' => $query->where(function (Builder $q) {
                $q->where('collection_category', 'general')
                    ->orWhere('type', MaterialType::Reading->value);
            }),
            'video' => $query->where(function (Builder $q) {
                $q->where('type', MaterialType::Video->value)
                    ->orWhere('content_format', 'video');
            }),
            'mine' => $query->where('created_by', $userId),
            default => $query,
        };
    }

    protected function getFilteredMaterialsQuery(): Builder
    {
        $userId = (int) auth()->id();
        $query = static::applyCategoryCondition(
            $this->getBaseVisibilityQuery(),
            $this->selectedCategory,
            $userId
        )->with([
            'subject:id,name,slug,icon,tone',
            'classroom:id,name,grade_level',
            'creator:id,name',
            'quizzes:id,material_id,title,status',
        ]);

        if (filled($this->searchQuery)) {
            $search = trim($this->searchQuery);
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhereHas('subject', fn (Builder $sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if (filled($this->subjectFilter)) {
            $query->where('subject_id', $this->subjectFilter);
        }

        if (filled($this->genreFilter)) {
            $query->where('genre', $this->genreFilter);
        }

        if (filled($this->gradeFilter)) {
            if ($this->gradeFilter === 'all') {
                $query->whereNull('target_grade')->whereNull('classroom_id');
            } else {
                $grade = $this->gradeFilter;
                $query->where(function (Builder $q) use ($grade) {
                    $q->where('target_grade', $grade)
                        ->orWhereHas('classroom', fn ($cq) => $cq->where('grade_level', $grade));
                });
            }
        }

        return $query->latest();
    }

    protected function getViewData(): array
    {
        $userId = (int) auth()->id();
        $baseQuery = $this->getBaseVisibilityQuery();

        $counts = [
            'learning' => static::applyCategoryCondition(clone $baseQuery, 'learning', $userId)->count(),
            'general' => static::applyCategoryCondition(clone $baseQuery, 'general', $userId)->count(),
            'video' => static::applyCategoryCondition(clone $baseQuery, 'video', $userId)->count(),
            'mine' => static::applyCategoryCondition(clone $baseQuery, 'mine', $userId)->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        $materials = $this->getFilteredMaterialsQuery()->get();

        $previewRecord = $this->previewMaterialId
            ? Material::with(['subject', 'classroom', 'creator'])->find($this->previewMaterialId)
            : null;

        $grades = Classroom::query()
            ->whereNotNull('grade_level')
            ->pluck('grade_level')
            ->unique()
            ->sort()
            ->values();

        return [
            'materials' => $materials,
            'counts' => $counts,
            'subjects' => Subject::orderBy('code')->get(['id', 'name']),
            'genres' => array_values(Material::GENRES),
            'grades' => $grades,
            'previewRecord' => $previewRecord,
            'resourceClass' => static::getResource(),
        ];
    }
}
