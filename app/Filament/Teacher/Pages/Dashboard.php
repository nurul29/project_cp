<?php

namespace App\Filament\Teacher\Pages;

use App\Filament\Teacher\Resources\MaterialResource;
use App\Filament\Teacher\Resources\QuizResource;
use App\Filament\Teacher\Widgets\TeacherOverview;
use App\Filament\Teacher\Widgets\TeacherStatsOverview;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Beranda Guru';

    protected static ?string $navigationLabel = 'Beranda';

    protected static ?int $navigationSort = 0;

    public function getTitle(): string
    {
        $name = auth()->user()?->name ?? 'Guru';
        return 'Selamat datang, ' . $name . '!';
    }

    public function getSubheading(): ?string
    {
        $subject = auth()->user()?->teachings?->pluck('subject.name')->first() ?? 'pembelajaran';
        return 'Kelola pembelajaran ' . $subject . ' dan pantau perkembangan siswa.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createMaterial')
                ->label('Tambah koleksi')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->url(MaterialResource::getUrl('create')),
            Action::make('createQuiz')
                ->label('Buat kuis')
                ->icon('heroicon-o-plus')
                ->color('gray')
                ->url(QuizResource::getUrl('create')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            TeacherOverview::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 1;
    }
}
