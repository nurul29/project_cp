<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Admin\Resources\UserResource;
use App\Models\Classroom;
use App\Models\Teaching;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        if ($record && $record->role === UserRole::Teacher) {
            $data['teaching_subject_ids'] = $record->teachings()->pluck('subject_id')->unique()->values()->all();
            $data['teaching_classroom_ids'] = $record->teachings()->pluck('classroom_id')->unique()->values()->all();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $rawState = $this->form->getRawState();
        $subjectIds = array_filter((array) ($rawState['teaching_subject_ids'] ?? []));

        if ($record->role === UserRole::Teacher && ! empty($subjectIds)) {
            $classroomIds = array_filter((array) ($rawState['teaching_classroom_ids'] ?? []));

            $record->teachings()->delete();
            foreach ($subjectIds as $subjectId) {
                $targetClassIds = $classroomIds;
                if (empty($targetClassIds)) {
                    // Ambil kelas yang belum diampu guru lain untuk mapel ini
                    $assignedClassIds = Teaching::where('subject_id', $subjectId)
                        ->where('teacher_id', '!=', $record->id)
                        ->pluck('classroom_id')
                        ->all();
                    $targetClassIds = Classroom::whereNotIn('id', $assignedClassIds)->pluck('id')->all();
                }

                foreach ($targetClassIds as $classroomId) {
                    $alreadyTaken = Teaching::where('subject_id', $subjectId)
                        ->where('classroom_id', $classroomId)
                        ->where('teacher_id', '!=', $record->id)
                        ->exists();

                    if (! $alreadyTaken) {
                        Teaching::firstOrCreate([
                            'teacher_id' => $record->id,
                            'subject_id' => $subjectId,
                            'classroom_id' => $classroomId,
                        ]);
                    }
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->successNotificationTitle('Pengguna berhasil dihapus')
                ->successRedirectUrl(UserResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perubahan pengguna berhasil disimpan';
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
