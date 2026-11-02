<?php

namespace App\Filament\Admin\Resources\MaterialResource\Pages;

use App\Filament\Admin\Resources\MaterialResource;
use App\Filament\Teacher\Resources\MaterialResource\Pages\ListMaterials as TeacherListMaterials;

class ListMaterials extends TeacherListMaterials
{
    protected static string $resource = MaterialResource::class;
}
