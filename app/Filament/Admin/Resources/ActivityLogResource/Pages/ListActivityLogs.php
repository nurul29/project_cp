<?php

namespace App\Filament\Admin\Resources\ActivityLogResource\Pages;

use App\Filament\Admin\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    public function getTitle(): string
    {
        return 'Riwayat aktivitas';
    }

    public function getSubheading(): ?string
    {
        return 'Catatan perubahan untuk membantu penelusuran pengelolaan sekolah.';
    }
}
