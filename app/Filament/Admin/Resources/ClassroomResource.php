<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClassroomResource\Pages;
use App\Models\Classroom;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $modelLabel = 'Kelas';

    protected static ?string $pluralModelLabel = 'Kelas';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('grade_level')
                ->label('Tingkat Kelas')
                ->options([
                    'VII' => 'Kelas VII',
                    'VIII' => 'Kelas VIII',
                    'IX' => 'Kelas IX',
                ])
                ->required()
                ->live()
                ->helperText('Pilih tingkat kelas terlebih dahulu. Tingkat kelas akan terkunci otomatis di depan nama rombel.'),

            Forms\Components\TextInput::make('name')
                ->label('Nama Kelas / Rombel')
                ->prefix(fn (Forms\Get $get): ?string => $get('grade_level') ?: null)
                ->placeholder(fn (Forms\Get $get): string => filled($get('grade_level')) ? 'Contoh: A, B, C' : 'Pilih tingkat kelas dahulu')
                ->helperText('Tingkat kelas (VII / VIII / IX) sudah terkunci otomatis di depan dan tidak bisa dihapus. Cukup ketik huruf rombelnya saja (A, B, C, dst.).')
                ->disabled(fn (Forms\Get $get): bool => blank($get('grade_level')))
                ->formatStateUsing(function (?string $state, Forms\Get $get): string {
                    if (blank($state)) {
                        return '';
                    }

                    return Classroom::extractRombelSuffix($state, $get('grade_level'));
                })
                ->dehydrateStateUsing(function (?string $state, Forms\Get $get): string {
                    return Classroom::formatFullClassroomName($get('grade_level'), (string) $state);
                })
                ->required()
                ->rule(function (Forms\Get $get, ?Classroom $record): \Closure {
                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                        $grade = $get('grade_level');
                        $suffix = Classroom::extractRombelSuffix((string) $value, $grade);

                        if ($suffix === '') {
                            $fail('Harap isi huruf/kode rombel kelas (contoh: A, B, C).');

                            return;
                        }

                        $fullName = Classroom::formatFullClassroomName($grade, (string) $value);
                        $exists = Classroom::query()
                            ->whereRaw('LOWER(name) = ?', [strtolower($fullName)])
                            ->when($record?->exists, fn ($q) => $q->where('id', '!=', $record->id))
                            ->exists();

                        if ($exists) {
                            $fail("Kelas '{$fullName}' sudah terdaftar dan tidak boleh ganda.");
                        }
                    };
                })
                ->maxLength(50),

            Forms\Components\Select::make('school_year')
                ->label('Tahun Ajaran')
                ->options(function () {
                    $current = (int) date('Y');
                    $years = [];
                    for ($y = $current - 2; $y <= $current + 2; $y++) {
                        $str = $y . '/' . ($y + 1);
                        $years[$str] = $str;
                    }
                    return $years;
                })
                ->default(function () {
                    $y = (int) date('Y');
                    $m = (int) date('n');
                    $startYear = $m < 7 ? $y - 1 : $y;
                    return $startYear . '/' . ($startYear + 1);
                })
                ->required(),

            Forms\Components\Select::make('homeroom_teacher_id')
                ->label('Wali Kelas')
                ->options(fn () => User::query()->where('role', 'teacher')->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->nullable(),

            Forms\Components\Toggle::make('is_active')->label('Kelas aktif')->default(true)->required(),
        ])->columns(2);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('homeroomTeacher')->ordered();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Kelas')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('grade_level')->label('Tingkat')->sortable(),
                Tables\Columns\TextColumn::make('school_year')->label('Tahun ajaran')->sortable(),
                Tables\Columns\TextColumn::make('homeroomTeacher.name')->label('Wali kelas')->placeholder('Belum ditentukan')->searchable(),
                Tables\Columns\TextColumn::make('students_count')->label('Siswa')->counts('students')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status kelas'),
                Tables\Filters\SelectFilter::make('grade_level')
                    ->label('Tingkat')
                    ->options([
                        'VII' => 'Kelas VII',
                        'VIII' => 'Kelas VIII',
                        'IX' => 'Kelas IX',
                    ]),
                Tables\Filters\SelectFilter::make('school_year')
                    ->label('Tahun Ajaran')
                    ->options(fn () => Classroom::query()->whereNotNull('school_year')->distinct()->pluck('school_year', 'school_year')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Classroom $record) => $record->is_active ? 'Arsipkan' : 'Aktifkan')
                    ->icon(fn (Classroom $record) => $record->is_active ? 'heroicon-o-archive-box-arrow-down' : 'heroicon-o-check-circle')
                    ->color(fn (Classroom $record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Classroom $record) => ($record->is_active ? 'Arsipkan' : 'Aktifkan') . " Kelas {$record->name}")
                    ->modalDescription(fn (Classroom $record) => $record->is_active
                        ? 'Kelas akan dinonaktifkan/diarsipkan. Riwayat belajar dan nilai siswa tetap tersimpan aman.'
                        : 'Kelas akan diaktifkan kembali.')
                    ->action(function (Classroom $record) {
                        $record->update(['is_active' => ! $record->is_active]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Status kelas berhasil diperbarui')
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Classroom $record) {
                        $reasons = $record->getDeletePreventionReasons();
                        if (! empty($reasons)) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Penghapusan Kelas Ditolak')
                                ->body(implode(' ', $reasons))
                                ->persistent()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
