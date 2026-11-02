<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Widgets\AdminOverview;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Pengelolaan perpustakaan';

    protected static ?string $navigationLabel = 'Ringkasan';

    protected static ?int $navigationSort = 0;

    public function getSubheading(): ?string
    {
        return 'Selamat datang. Berikut hal yang perlu kamu pantau hari ini.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createUser')
                ->label('Tambah pengguna')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(UserResource::getUrl('create')),
        ];
    }

    public function getWidgets(): array
    {
        return [
            AdminOverview::class,
        ];
    }
}
