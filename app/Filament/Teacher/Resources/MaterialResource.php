<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Enums\VideoProvider;
use App\Filament\Teacher\Resources\MaterialResource\Pages;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use App\Support\YouTube;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Koleksi Digital';

    protected static ?string $modelLabel = 'Koleksi Digital';

    protected static ?string $pluralModelLabel = 'Koleksi Digital';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['subject', 'classroom', 'creator']);
    }

    public static function resolvePreviewPdfUrl(mixed $fileState, ?Material $record = null): ?string
    {
        if (is_array($fileState)) {
            $fileState = reset($fileState);
        }

        if ($fileState instanceof TemporaryUploadedFile) {
            return route('material.temp-preview', ['filename' => $fileState->getFilename()]);
        }

        if (is_string($fileState) && $fileState !== '') {
            return Storage::url($fileState);
        }

        if ($record?->file_path) {
            return Storage::url($record->file_path);
        }

        return null;
    }

    /**
     * Ambil daftar mata pelajaran berdasarkan kewenangan pengguna.
     * Admin: seluruh mata pelajaran.
     * Guru: hanya mata pelajaran yang diampunya.
     *
     * @return array<int, string>
     */
    public static function getSubjectOptionsForUser(): array
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin()) {
            return Subject::orderBy('code')->pluck('name', 'id')->all();
        }

        if ($user->isTeacher()) {
            $teachingSubjectIds = $user->teachings()
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();

            if ($teachingSubjectIds->isNotEmpty()) {
                return Subject::whereIn('id', $teachingSubjectIds)
                    ->orderBy('code')
                    ->pluck('name', 'id')
                    ->all();
            }
        }

        return Subject::orderBy('code')->pluck('name', 'id')->all();
    }

    /**
     * Ambil daftar jenjang tingkat kelas tujuan.
     *
     * @return array<string, string>
     */
    public static function getTargetGradeOptionsForUser(): array
    {
        $user = auth()->user();

        $allGrades = Classroom::query()
            ->whereNotNull('grade_level')
            ->pluck('grade_level')
            ->unique()
            ->sort()
            ->values();

        $allGradeOptions = [];
        foreach ($allGrades as $grade) {
            $allGradeOptions[$grade] = 'Kelas ' . $grade;
        }

        if (! $user || $user->isAdmin()) {
            return $allGradeOptions;
        }

        if ($user->isTeacher()) {
            $teacherGrades = $user->teachings()
                ->with('classroom')
                ->get()
                ->pluck('classroom.grade_level')
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($teacherGrades->isNotEmpty()) {
                $options = [];
                foreach ($teacherGrades as $grade) {
                    $options[$grade] = 'Kelas ' . $grade;
                }
                return $options;
            }
        }

        return $allGradeOptions;
    }

    /**
     * Ambil pilihan kelas spesifik untuk guru/admin.
     *
     * @return array<int, string>
     */
    public static function getClassroomOptionsForUser(): array
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin()) {
            return Classroom::ordered()->pluck('name', 'id')->all();
        }

        if ($user->isTeacher()) {
            $classIds = $user->teachings()->pluck('classroom_id')->filter()->unique()->values();
            if ($classIds->isNotEmpty()) {
                return Classroom::whereIn('id', $classIds)->ordered()->pluck('name', 'id')->all();
            }
        }

        return Classroom::ordered()->pluck('name', 'id')->all();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kategori & Format Koleksi')
                    ->description('Tentukan kelompok koleksi dan jenis format konten yang akan diunggah.')
                    ->schema([
                        Forms\Components\Select::make('collection_category')
                            ->label('Kategori Koleksi')
                            ->options([
                                'learning' => 'Materi Pembelajaran (Buku pelajaran, modul, dan video pendukung)',
                                'general' => 'Bacaan Umum (Buku non-mapel: novel, cerpen, dongeng, biografi, dsb.)',
                            ])
                            ->default('learning')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'general') {
                                    $set('subject_id', null);
                                    $set('target_grade', null);
                                    $set('target_classroom_ids', null);
                                    $set('classroom_id', null);
                                    $set('url', null);
                                    $set('video_provider', null);
                                    $set('content_format', 'pdf');
                                    $set('genre', array_key_first(Material::GENRES));
                                } else {
                                    $set('genre', null);
                                    $set('author', null);
                                    $set('content_format', 'pdf');
                                    $subjects = self::getSubjectOptionsForUser();
                                    $set('subject_id', array_key_first($subjects));
                                }
                            }),

                        Forms\Components\Select::make('content_format')
                            ->label('Format Konten')
                            ->options([
                                'pdf' => 'Dokumen PDF (Buku digital / Modul materi)',
                                'video' => 'Video Pembelajaran (YouTube / Video Web)',
                            ])
                            ->default('pdf')
                            ->visible(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning')
                            ->required(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning')
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'video') {
                                    $set('file_path', null);
                                } else {
                                    $set('url', null);
                                    $set('video_provider', null);
                                }
                            }),
                    ])->columns(2),

                // Form Materi Pembelajaran
                Forms\Components\Section::make('Informasi Materi Pembelajaran')
                    ->description('Kelola modul dan materi pembelajaran kelas yang terhubung dengan mata pelajaran dan tingkat kelas.')
                    ->visible(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Materi Pembelajaran')
                            ->required(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning')
                            ->maxLength(150)
                            ->placeholder('Contoh: Teorema Pythagoras & Segitiga Siku-siku'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('subject_id')
                                    ->label('Mata Pelajaran')
                                    ->options(fn (): array => self::getSubjectOptionsForUser())
                                    ->default(fn () => array_key_first(self::getSubjectOptionsForUser()))
                                    ->required(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning')
                                    ->live()
                                    ->helperText('Otomatis menyesuaikan dengan mata pelajaran yang Anda ampu.'),

                                Forms\Components\Select::make('target_grade')
                                    ->label('Jenjang Tingkat Kelas')
                                    ->options(fn (): array => self::getTargetGradeOptionsForUser())
                                    ->placeholder('Semua Jenjang Kelas')
                                    ->nullable()
                                    ->helperText('Pilih tingkat kelas atau biarkan kosong untuk semua kelas.'),
                            ]),

                        Forms\Components\Select::make('target_classroom_ids')
                            ->label('Kelas Tujuan Spesifik (Opsional)')
                            ->options(fn (): array => self::getClassroomOptionsForUser())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Pilih satu atau beberapa rombel kelas. Biarkan kosong jika materi berlaku umum untuk seluruh kelas.'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi / Petunjuk Singkat')
                            ->rows(3)
                            ->placeholder('Jelaskan materi atau petunjuk belajar bagi siswa...'),
                    ]),

                // Form Bacaan Umum
                Forms\Components\Section::make('Informasi Bacaan Umum')
                    ->description('Buku bacaan umum dapat diakses oleh seluruh siswa tanpa terikat mata pelajaran tertentu.')
                    ->visible(fn (Get $get) => $get('collection_category') === 'general')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Buku / Bahan Bacaan')
                            ->required(fn (Get $get) => $get('collection_category') === 'general')
                            ->maxLength(150)
                            ->placeholder('Contoh: Antologi Cerpen Remaja / Laskar Pelangi'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('genre')
                                    ->label('Genre / Kategori Bacaan')
                                    ->options(Material::GENRES)
                                    ->default(array_key_first(Material::GENRES))
                                    ->required(fn (Get $get) => $get('collection_category') === 'general'),

                                Forms\Components\TextInput::make('author')
                                    ->label('Penulis / Pengarang (Opsional)')
                                    ->maxLength(150)
                                    ->placeholder('Nama penulis atau penerbit buku...'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi / Ringkasan Buku')
                            ->rows(3)
                            ->placeholder('Sinopsis atau gambaran isi buku bacaan...'),
                    ]),

                // Bagian Berkas / Media
                Forms\Components\Section::make('Sumber Berkas & Pratinjau')
                    ->schema([
                        // Unggah PDF
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Unggah Berkas PDF (Maks. 50 MB)')
                            ->disk('public')
                            ->directory('materials')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200)
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && ($get('collection_category') === 'general' || ($get('content_format') ?? 'pdf') === 'pdf'))
                            ->visible(fn (Get $get) => $get('collection_category') === 'general' || ($get('content_format') ?? 'pdf') === 'pdf')
                            ->helperText('File format .pdf. Siswa dapat membaca langsung di aplikasi menggunakan In-App PDF Reader.'),

                        Forms\Components\Placeholder::make('pdf_preview')
                            ->label('Pratinjau Dokumen')
                            ->visible(fn (Get $get) => $get('collection_category') === 'general' || ($get('content_format') ?? 'pdf') === 'pdf')
                            ->content(function (Get $get, ?Material $record): HtmlString {
                                $pdfUrl = self::resolvePreviewPdfUrl($get('file_path'), $record);

                                if (! $pdfUrl) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-xs text-gray-500">Unggah file PDF di atas untuk melihat pratinjau dokumen.</div>');
                                }

                                return new HtmlString('
                                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-inner">
                                        <div class="flex items-center justify-between border-b border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700">
                                            <span>Dokumen Terbuka</span>
                                            <a href="'.$pdfUrl.'" target="_blank" rel="noopener" class="text-teal-700 hover:underline">Buka Tab Penuh ↗</a>
                                        </div>
                                        <iframe src="'.$pdfUrl.'#zoom=100" class="h-96 w-full border-0 bg-white" title="Pratinjau PDF"></iframe>
                                    </div>
                                ');
                            }),

                        // Tautan Video
                        Forms\Components\TextInput::make('url')
                            ->label('Tautan Video Pembelajaran')
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->visible(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning' && $get('content_format') === 'video')
                            ->required(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning' && $get('content_format') === 'video')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $provider = VideoProvider::fromUrl($state);
                                $set('video_provider', $provider?->value ?? VideoProvider::YouTube->value);
                            })
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if ($value && ! YouTube::isEmbeddable($value)) {
                                        $fail('Pemilik video YouTube ini menonaktifkan pemutaran di situs lain sehingga tidak bisa diputar di SUNDIGITAL. Gunakan tautan video lain.');
                                    }
                                },
                            ])
                            ->helperText('Mendukung video YouTube yang mengizinkan pemutaran di situs lain. Siswa dapat menonton langsung di aplikasi.'),

                        Forms\Components\Placeholder::make('video_preview')
                            ->label('Pemutar Video Dalam Aplikasi')
                            ->visible(fn (Get $get) => ($get('collection_category') ?? 'learning') === 'learning' && $get('content_format') === 'video')
                            ->content(function (Get $get, ?Material $record): HtmlString {
                                $url = $get('url') ?: $record?->url;
                                $embedUrl = YouTube::embedUrl($url);

                                if (! $embedUrl) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-xs text-gray-500">Masukkan tautan YouTube yang valid di atas untuk melihat pemutar video.</div>');
                                }

                                return new HtmlString('
                                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-black shadow-inner">
                                        <div class="flex items-center justify-between border-b border-gray-800 bg-gray-900 px-3 py-2 text-xs font-medium text-gray-200">
                                            <span>Pemutar Video Langsung (In-App Player)</span>
                                            <a href="'.$url.'" target="_blank" rel="noopener" class="text-teal-400 hover:underline">Buka YouTube ↗</a>
                                        </div>
                                        <iframe src="'.$embedUrl.'" class="aspect-video w-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="Video Pembelajaran"></iframe>
                                    </div>
                                ');
                            }),
                    ]),

                Forms\Components\Section::make('Status Publikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draf (Hanya terlihat oleh Anda)',
                                'published' => 'Terbit (Dapat diakses oleh siswa)',
                            ])
                            ->default('published')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction('preview')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Koleksi')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('collection_category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'general' ? 'Bacaan Umum' : 'Materi Pelajaran')
                    ->color(fn ($state) => $state === 'general' ? 'warning' : 'info'),

                Tables\Columns\TextColumn::make('content_format')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn ($state, Material $record) => ($state === 'video' || $record->type?->value === 'video') ? '🎬 Video' : '📄 PDF')
                    ->color(fn ($state, Material $record) => ($state === 'video' || $record->type?->value === 'video') ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('category_display')
                    ->label('Mata Pelajaran / Genre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('target_class_display')
                    ->label('Kelas Tujuan')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('author')
                    ->label('Penulis')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pengunggah')
                    ->default('Sistem')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => ($state instanceof PublicationStatus ? $state->value : $state) === PublicationStatus::Published->value ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => ($state instanceof PublicationStatus ? $state->value : $state) === PublicationStatus::Published->value ? 'Terbit' : 'Draf'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_category')
                    ->label('Kategori Koleksi')
                    ->options([
                        'learning' => 'Materi Pembelajaran',
                        'general' => 'Bacaan Umum',
                    ]),
                Tables\Filters\SelectFilter::make('content_format')
                    ->label('Format Konten')
                    ->options([
                        'pdf' => 'Dokumen PDF',
                        'video' => 'Video Pembelajaran',
                    ]),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->options(fn () => Subject::orderBy('code')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Material $record): string => 'Pratinjau Koleksi: '.$record->title)
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Material $record) => view('filament.components.material-modal-preview', [
                        'record' => $record,
                    ])),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Material $record) => auth()->user()->isAdmin() || (int) $record->created_by === (int) auth()->id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Material $record) => auth()->user()->isAdmin() || (int) $record->created_by === (int) auth()->id()),
            ]);
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
