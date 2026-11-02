<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MaterialResource\Pages;
use App\Filament\Teacher\Resources\MaterialResource as TeacherMaterialResource;
use App\Models\Material;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Koleksi Digital';

    protected static ?string $modelLabel = 'Koleksi Digital';

    protected static ?string $pluralModelLabel = 'Koleksi Digital';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['subject', 'classroom', 'creator']);
    }

    public static function form(Form $form): Form
    {
        return TeacherMaterialResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return TeacherMaterialResource::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
