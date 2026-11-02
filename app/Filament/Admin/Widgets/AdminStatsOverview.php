<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\AccountRequestStatus;
use App\Models\AccountRequest;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Pengguna aktif', User::query()->where('is_active', true)->count())
                ->description('Siswa, guru, dan administrator')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Kelas aktif', Classroom::query()->where('is_active', true)->count())
                ->description('Kelas belajar terdaftar')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('success'),
            Stat::make('Mata pelajaran', Subject::query()->count())
                ->description('Kategori pembelajaran')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),
            Stat::make(
                'Bantuan tertunda',
                AccountRequest::query()->where('status', AccountRequestStatus::Pending->value)->count(),
            )
                ->description('Menunggu tindak lanjut')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('warning'),
        ];
    }
}
