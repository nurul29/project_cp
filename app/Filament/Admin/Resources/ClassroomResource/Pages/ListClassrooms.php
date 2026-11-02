<?php

namespace App\Filament\Admin\Resources\ClassroomResource\Pages;

use App\Filament\Admin\Resources\ClassroomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    public function getTitle(): string
    {
        return 'Kelas & wali kelas';
    }

    public function getSubheading(): ?string
    {
        return 'Atur kelompok belajar dan penanggung jawab kelas.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Tambah kelas'),
        ];
    }
}
