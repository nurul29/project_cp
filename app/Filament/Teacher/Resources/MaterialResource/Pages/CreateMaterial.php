<?php

namespace App\Filament\Teacher\Resources\MaterialResource\Pages;

use App\Filament\Teacher\Resources\MaterialResource;
use App\Filament\Teacher\Resources\QuizResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected static bool $canCreateAnother = false;

    public bool $shouldCreateQuizAfter = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        if ($this->shouldCreateQuizAfter && $this->record) {
            return QuizResource::getUrl('create', [
                'material_id' => $this->record->id,
            ]);
        }

        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Koleksi berhasil ditambahkan');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),

            Action::make('saveAndAddQuiz')
                ->label('Simpan dan Tambah Kuis')
                ->color('warning')
                ->icon('heroicon-o-academic-cap')
                ->action(function () {
                    $this->shouldCreateQuizAfter = true;
                    $this->create();
                }),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }
}
