<?php

namespace App\Filament\Teacher\Resources\LibraryResource\Pages;

use App\Filament\Teacher\Resources\LibraryResource;
use App\Models\Subject;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateLibrary extends CreateRecord
{
    protected static string $resource = LibraryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (($data['type'] ?? null) === 'reading') {
            if (empty($data['subject_id'])) {
                $data['subject_id'] = Subject::where('slug', 'bahasa-indonesia')->value('id') ?? Subject::query()->value('id');
            }
            if (! empty($data['genre']) && empty($data['cover_text'])) {
                $data['cover_text'] = Str::limit($data['genre'], 28, '');
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
