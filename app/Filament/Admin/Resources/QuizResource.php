<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuizResource\Pages;
use App\Filament\Teacher\Resources\QuizResource as TeacherQuizResource;
use App\Models\Quiz;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Kelola Kuis';

    protected static ?string $modelLabel = 'Kuis';

    protected static ?string $pluralModelLabel = 'Kelola Kuis';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['subject', 'classroom', 'material', 'creator']);
    }

    public static function form(Form $form): Form
    {
        return TeacherQuizResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return TeacherQuizResource::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'view' => Pages\ViewQuiz::route('/{record}'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
