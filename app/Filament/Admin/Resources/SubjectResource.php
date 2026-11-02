<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubjectResource\Pages;
use App\Filament\Admin\Resources\SubjectResource\RelationManagers\TeachingsRelationManager;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Mata pelajaran';

    protected static ?string $modelLabel = 'Mata pelajaran';

    protected static ?string $pluralModelLabel = 'Mata pelajaran';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama mata pelajaran')
                ->required()
                ->maxLength(255)
                ->placeholder('Contoh: Pendidikan Agama Islam'),
            Forms\Components\TextInput::make('code')
                ->label('Kode mata pelajaran')
                ->required()
                ->placeholder('Contoh: PAI01, MTK, IPA')
                ->maxLength(30)
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->label('Deskripsi')
                ->placeholder('Deskripsi atau keterangan singkat mata pelajaran (opsional)...')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label('Ikon')->alignCenter(),
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Mata pelajaran')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('teachings_count')->label('Penugasan Guru')->counts('teachings')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [TeachingsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
