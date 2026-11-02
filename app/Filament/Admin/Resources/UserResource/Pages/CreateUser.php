<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Admin\Resources\UserResource;
use App\Models\Classroom;
use App\Models\Teaching;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengguna berhasil ditambahkan');
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $role = $data['role'] ?? null;
        $isStudent = $role === UserRole::Student->value || $role === 'student' || $role === UserRole::Student;

        if ($isStudent && empty($data['password']) && ! empty($data['username'])) {
            $plainPassword = UserResource::generateStudentPassword((string) $data['username']);
            $data['password'] = \Illuminate\Support\Facades\Hash::make($plainPassword);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $rawState = $this->form->getRawState();
        $subjectIds = array_filter((array) ($rawState['teaching_subject_ids'] ?? []));

        if ($record->role === UserRole::Teacher && ! empty($subjectIds)) {
            $classroomIds = array_filter((array) ($rawState['teaching_classroom_ids'] ?? []));

            foreach ($subjectIds as $subjectId) {
                $targetClassIds = $classroomIds;
                if (empty($targetClassIds)) {
                    // Ambil kelas yang belum diampu guru manapun untuk mapel ini
                    $assignedClassIds = Teaching::where('subject_id', $subjectId)->pluck('classroom_id')->all();
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
}
