<?php

namespace App\Filament\Teacher\Resources\VideoResource\Pages;

use App\Enums\MaterialType;
use App\Filament\Teacher\Resources\VideoResource;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ListVideos extends ListRecords
{
    protected static string $resource = VideoResource::class;

    protected static string $view = 'filament.teacher.pages.list-materials';

    public string $searchQuery = '';

    public ?int $subjectFilter = null;

    public ?string $typeFilter = null;

    public ?string $gradeFilter = null;

    public ?int $previewMaterialId = null;

    public function getSubheading(): ?string
    {
        return 'Kelola video pembelajaran interaktif yang dapat ditonton langsung oleh siswa di aplikasi SUNDIGITAL.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah video pembelajaran'),
        ];
    }

    public function resetCardFilters(): void
    {
        $this->searchQuery = '';
        $this->subjectFilter = null;
        $this->typeFilter = null;
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

        if (! auth()->user()?->can('delete', $material)) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Anda tidak memiliki izin untuk menghapus video ini.')
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
                ->title('Video berhasil dihapus')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Tidak dapat menghapus video')
                ->body(collect($e->errors())->flatten()->first() ?? 'Video masih digunakan.')
                ->danger()
                ->send();
        }
    }

    protected function getFilteredMaterialsQuery(): Builder
    {
        $query = Material::query()
            ->where('type', MaterialType::Video->value)
            ->with(['subject', 'classroom', 'creator']);

        if (filled($this->searchQuery)) {
            $search = trim($this->searchQuery);
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (filled($this->subjectFilter)) {
            $query->where('subject_id', $this->subjectFilter);
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
        $userId = auth()->id();
        $allMaterials = $this->getFilteredMaterialsQuery()->get();

        /** @var Collection<int, Material> $myMaterials */
        $myMaterials = $allMaterials->filter(fn (Material $m) => (int) $m->created_by === (int) $userId)->values();

        /** @var Collection<int, Material> $otherMaterials */
        $otherMaterials = $allMaterials->filter(fn (Material $m) => (int) $m->created_by !== (int) $userId)->values();

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
            'myMaterials' => $myMaterials,
            'otherMaterials' => $otherMaterials,
            'subjects' => Subject::orderBy('name')->get(['id', 'name']),
            'grades' => $grades,
            'previewRecord' => $previewRecord,
            'pageTitle' => 'Video Pembelajaran',
            'allowedTypes' => ['video' => 'Video Pembelajaran'],
        ];
    }
}
