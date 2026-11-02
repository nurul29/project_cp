<?php

namespace App\Filament\Teacher\Resources\VideoResource\Pages;

use App\Enums\MaterialType;
use App\Enums\VideoProvider;
use App\Filament\Teacher\Resources\VideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = MaterialType::Video->value;
        $data['cover_text'] = Str::limit($data['title'] ?? 'Video Pembelajaran', 28, '');

        if (! empty($data['url'])) {
            $data['video_provider'] = (VideoProvider::fromUrl($data['url']) ?? VideoProvider::YouTube)->value;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
