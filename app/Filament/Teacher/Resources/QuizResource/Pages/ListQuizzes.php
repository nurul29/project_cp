<?php

namespace App\Filament\Teacher\Resources\QuizResource\Pages;

use App\Filament\Teacher\Resources\QuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizResource::class;

    public function getSubheading(): ?string
    {
        return 'Rancang kuis pilihan ganda untuk menguji pemahaman siswa pada materi yang dipelajari.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Buat kuis'),
        ];
    }
}
