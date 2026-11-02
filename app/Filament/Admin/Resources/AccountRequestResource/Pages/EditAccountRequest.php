<?php

namespace App\Filament\Admin\Resources\AccountRequestResource\Pages;

use App\Filament\Admin\Resources\AccountRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountRequest extends EditRecord
{
    protected static string $resource = AccountRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['handled_by'] = auth()->id();
        $data['handled_at'] = in_array($data['status'], ['resolved', 'rejected'], true) ? now() : null;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
