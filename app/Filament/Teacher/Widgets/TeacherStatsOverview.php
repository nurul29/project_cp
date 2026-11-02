<?php

namespace App\Filament\Teacher\Widgets;

use App\Enums\PublicationStatus;
use App\Enums\MaterialType;
use App\Models\Material;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class TeacherStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $teachings = $user->teachings ?? collect();
        $subjectIds = $teachings->pluck('subject_id')->unique();
        $classroomIds = $teachings->pluck('classroom_id')->unique();
        $gradeLevels = Classroom::whereIn('id', $classroomIds)->pluck('grade_level')->filter()->unique();
        $scopeMaterials = fn (Builder $query) => $query->where(function (Builder $query) use ($user, $subjectIds, $classroomIds, $gradeLevels) {
            $query->where('created_by', $user->id)
                ->orWhere(function (Builder $query) use ($subjectIds, $classroomIds, $gradeLevels) {
                    $query->whereIn('subject_id', $subjectIds)
                        ->where(fn (Builder $query) => $query->where(function ($sub) {
                            $sub->whereNull('classroom_id')->whereNull('target_grade');
                        })
                        ->orWhereIn('classroom_id', $classroomIds)
                        ->orWhereIn('target_grade', $gradeLevels));
                });
        });

        $libraryQuery = Material::query()
            ->where('type', '!=', MaterialType::Video->value)
            ->where($scopeMaterials);
        $publishedCount = (clone $libraryQuery)->where('status', PublicationStatus::Published->value)->count();
        $draftCount = (clone $libraryQuery)->where('status', PublicationStatus::Draft->value)->count();
        $supportCount = Material::query()
            ->where('type', MaterialType::Video->value)
            ->where($scopeMaterials)
            ->count();

        return [
            Stat::make('Buku & bacaan', (clone $libraryQuery)->count())
                ->description('PDF, modul, dan koleksi contoh')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('primary'),
            Stat::make('Koleksi terbit', $publishedCount)
                ->description('Tersedia di kelas tujuan')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),
            Stat::make('Draf koleksi', $draftCount)
                ->description('Siap dilengkapi dan ditinjau')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('warning'),
            Stat::make('Materi pendukung', $supportCount)
                ->description('Tautan video pembelajaran')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),
        ];
    }
}
