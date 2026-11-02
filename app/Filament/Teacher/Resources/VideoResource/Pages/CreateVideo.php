<?php

namespace App\Filament\Teacher\Resources\VideoResource\Pages;

use App\Enums\MaterialType;
use App\Enums\VideoProvider;
use App\Filament\Teacher\Resources\VideoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = MaterialType::Video->value;
        $data['created_by'] = auth()->id();
        $data['cover_text'] = Str::limit($data['title'] ?? 'Video Pembelajaran', 28, '');

        if (! empty($data['url'])) {
            $data['video_provider'] = (VideoProvider::fromUrl($data['url']) ?? VideoProvider::YouTube)->value;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
