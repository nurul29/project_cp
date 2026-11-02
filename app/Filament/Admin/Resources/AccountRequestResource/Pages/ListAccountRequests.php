<?php

namespace App\Filament\Admin\Resources\AccountRequestResource\Pages;

use App\Filament\Admin\Resources\AccountRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListAccountRequests extends ListRecords
{
    protected static string $resource = AccountRequestResource::class;

    public function getTitle(): string
    {
        return 'Permintaan bantuan akun';
    }

    public function getSubheading(): ?string
    {
        return 'Tinjau permintaan dan verifikasi identitas sebelum mengubah akses.';
    }
}
