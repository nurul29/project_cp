<?php

namespace App\Filament\Admin\Resources\SubjectResource\RelationManagers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Teaching;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TeachingsRelationManager extends RelationManager
{
    protected static string $relationship = 'teachings';

    protected static ?string $title = 'Penugasan Guru';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('teacher_id')
                ->label('Guru')
                ->options(fn () => User::query()->where('role', UserRole::Teacher)->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('classroom_id')
                ->label('Kelas')
                ->options(function (?Model $record) {
                    $subject = $this->getOwnerRecord();
                    $assignedClassroomIds = Teaching::where('subject_id', $subject->id)
                        ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                        ->pluck('classroom_id')
                        ->all();

                    return Classroom::query()
                        ->where('is_active', true)
                        ->whereNotIn('id', $assignedClassroomIds)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })
                ->searchable()
                ->preload()
                ->required()
                ->rules([
                    fn (?Model $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($record) {
                        $subject = $this->getOwnerRecord();
                        $existing = Teaching::where('subject_id', $subject->id)
                            ->where('classroom_id', $value)
                            ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                            ->with('teacher')
                            ->first();

                        if ($existing) {
                            $teacherName = $existing->teacher?->name ?? 'guru lain';
                            $fail("Kelas ini sudah diampu oleh {$teacherName} untuk mata pelajaran {$subject->name}. Satu kelas hanya boleh diampu oleh satu guru.");
                        }
                    },
                ])
                ->helperText('Hanya menampilkan kelas yang belum memiliki guru pengampu untuk mata pelajaran ini.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')->label('Guru')->searchable(),
                Tables\Columns\TextColumn::make('classroom.name')->label('Kelas')->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah penugasan'),
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
}
