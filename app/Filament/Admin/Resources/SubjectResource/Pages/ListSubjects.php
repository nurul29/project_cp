<?php

namespace App\Filament\Admin\Resources\SubjectResource\Pages;

use App\Filament\Admin\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    public function getTitle(): string
    {
        return 'Mata pelajaran';
    }

    public function getSubheading(): ?string
    {
        return 'Lengkapi daftar pelajaran dan guru pengampu.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah pelajaran'),
        ];
    }
}
