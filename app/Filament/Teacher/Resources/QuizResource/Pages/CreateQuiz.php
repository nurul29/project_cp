<?php

namespace App\Filament\Teacher\Resources\QuizResource\Pages;

use App\Filament\Teacher\Resources\QuizResource;
use App\Models\Material;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Kuis berhasil ditambahkan');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($materialId = request()->query('material_id')) {
            $material = Material::find($materialId);
            if ($material) {
                $data['material_id'] = $material->id;
                $data['subject_id'] = $material->subject_id;
                $data['classroom_id'] = $material->classroom_id;
                $data['title'] = 'Kuis: ' . $material->title;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
