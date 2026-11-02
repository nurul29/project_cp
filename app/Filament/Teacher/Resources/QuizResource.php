<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\PublicationStatus;
use App\Filament\Teacher\Resources\QuizResource\Pages;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Kelola Kuis';

    protected static ?string $modelLabel = 'Kuis';

    protected static ?string $pluralModelLabel = 'Kelola Kuis';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kuis')
                    ->description('Tentukan judul, kelas tujuan, dan keterkaitan materi bacaan.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Kuis')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Latihan Soal Teorema Pythagoras'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('subject_id')
                                    ->label('Mata Pelajaran')
                                    ->options(function () {
                                        $user = auth()->user();
                                        if ($user && $user->isTeacher()) {
                                            $teachingSubjects = $user->teachings()->pluck('subject_id');
                                            return Subject::whereIn('id', $teachingSubjects)->pluck('name', 'id');
                                        }
                                        return Subject::pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live(),

                                Forms\Components\Select::make('classroom_id')
                                    ->label('Kelas Tujuan')
                                    ->options(function () {
                                        $user = auth()->user();
                                        if ($user && $user->isTeacher()) {
                                            $teachingClasses = $user->teachings()->pluck('classroom_id');
                                            return Classroom::whereIn('id', $teachingClasses)->pluck('name', 'id');
                                        }
                                        return Classroom::pluck('name', 'id');
                                    })
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('material_id')
                            ->label('Keterkaitan dengan Materi Buku')
                            ->options(function (Forms\Get $get) {
                                $query = Material::query();
                                if ($subjectId = $get('subject_id')) {
                                    $query->where('subject_id', $subjectId);
                                }
                                return $query->pluck('title', 'id');
                            })
                            ->placeholder('Kuis Mandiri (Tidak tertaut ke buku tertentu)')
                            ->helperText('Jika dipilih, tombol kuis ini akan otomatis muncul pada pembaca PDF siswa saat membaca buku tersebut.')
                            ->nullable(),

                        Forms\Components\Textarea::make('instructions')
                            ->label('Petunjuk Pengerjaan')
                            ->default('Pilih satu jawaban yang paling tepat pada setiap soal.')
                            ->rows(2),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Kuis')
                                    ->options([
                                        'published' => 'Terbit (Dapat dikerjakan siswa)',
                                        'draft' => 'Draf (Disimpan sementara)',
                                    ])
                                    ->default('published')
                                    ->required(),

                                Forms\Components\TextInput::make('revision')
                                    ->label('Versi Revisi')
                                    ->default(1)
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Forms\Components\Section::make('Butir Soal Pilihan Ganda (A, B, C, D)')
                    ->description('Tambahkan pertanyaan, 4 opsi jawaban, 1 kunci jawaban, dan pembahasan.')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship('questions')
                            ->orderColumn('position')
                            ->defaultItems(1)
                            ->cloneable()
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['body']) ? Str::limit(strip_tags($state['body']), 60) : 'Butir Soal Baru')
                            ->schema([
                                Forms\Components\Textarea::make('body')
                                    ->label('Pertanyaan / Soal')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Tuliskan teks pertanyaan soal pilihan ganda...'),

                                Forms\Components\Fieldset::make('Pilihan Jawaban (A, B, C, D)')
                                    ->schema([
                                        Forms\Components\TextInput::make('options.0')
                                            ->label('Pilihan A')
                                            ->required(),
                                        Forms\Components\TextInput::make('options.1')
                                            ->label('Pilihan B')
                                            ->required(),
                                        Forms\Components\TextInput::make('options.2')
                                            ->label('Pilihan C')
                                            ->required(),
                                        Forms\Components\TextInput::make('options.3')
                                            ->label('Pilihan D')
                                            ->required(),
                                    ]),

                                Forms\Components\Radio::make('correct_index')
                                    ->label('Kunci Jawaban Benar')
                                    ->options([
                                        0 => 'Kunci: Pilihan A',
                                        1 => 'Kunci: Pilihan B',
                                        2 => 'Kunci: Pilihan C',
                                        3 => 'Kunci: Pilihan D',
                                    ])
                                    ->inline()
                                    ->required(),

                                Forms\Components\Textarea::make('explanation')
                                    ->label('Pembahasan Jawaban (Tampil setelah siswa selesai mengerjakan)')
                                    ->rows(2)
                                    ->placeholder('Tuliskan langkah penyelesaian atau penjelasan mengapa jawaban tersebut benar...'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Kuis')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable(),

                Tables\Columns\TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->badge(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pembuat')
                    ->default('Guru')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('material.title')
                    ->label('Materi Tertaut')
                    ->default('✦ Kuis Mandiri')
                    ->badge()
                    ->color(fn ($record) => $record->material_id ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->badge(),

                Tables\Columns\TextColumn::make('attempts_stats')
                    ->label('Partisipasi Siswa')
                    ->state(function (Quiz $record): string {
                        $totalStudents = $record->classroom?->students()->count() ?? 0;
                        $attemptedStudents = $record->attempts()->distinct('student_id')->count('student_id');
                        return "{$attemptedStudents} / {$totalStudents} Siswa";
                    })
                    ->badge()
                    ->color(function (Quiz $record): string {
                        $totalStudents = $record->classroom?->students()->count() ?? 0;
                        if ($totalStudents === 0) {
                            return 'gray';
                        }
                        $attemptedStudents = $record->attempts()->distinct('student_id')->count('student_id');
                        if ($attemptedStudents === 0) {
                            return 'warning';
                        }
                        if ($attemptedStudents >= $totalStudents) {
                            return 'success';
                        }
                        return 'info';
                    }),

                Tables\Columns\TextColumn::make('revision')
                    ->label('Versi')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => 'v'.$state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => ($state instanceof PublicationStatus ? $state->value : $state) === PublicationStatus::Published->value ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => ($state instanceof PublicationStatus ? $state->value : $state) === PublicationStatus::Published->value ? 'Terbit' : 'Draf'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name'),

                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'published' => 'Terbit',
                        'draft' => 'Draf',
                    ]),
            ])
            ->actionsColumnLabel('Aksi')
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail & Hasil')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Quiz $record): string => static::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (Quiz $record) => auth()->user()->isAdmin() || (int) $record->created_by === (int) auth()->id() || auth()->user()->teachings()->where('subject_id', $record->subject_id)->where('classroom_id', $record->classroom_id)->exists()),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (Quiz $record) => auth()->user()->isAdmin() || (int) $record->created_by === (int) auth()->id() || auth()->user()->teachings()->where('subject_id', $record->subject_id)->where('classroom_id', $record->classroom_id)->exists()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->isTeacher()) {
            $teachings = $user->teachings;
            $subjectIds = $teachings->pluck('subject_id')->unique();
            $classroomIds = $teachings->pluck('classroom_id')->unique();

            return $query->where(function (Builder $q) use ($user, $subjectIds, $classroomIds) {
                $q->where('created_by', $user->id)
                    ->orWhere(function (Builder $sub) use ($subjectIds, $classroomIds) {
                        $sub->whereIn('subject_id', $subjectIds)
                            ->whereIn('classroom_id', $classroomIds);
                    });
            });
        }

        return $query;
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
