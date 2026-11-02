<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Filament\Teacher\Resources\LibraryResource\Pages;
use App\Models\Classroom;
use App\Models\Material;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class LibraryResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Perpustakaan Sekolah';

    protected static ?string $modelLabel = 'Buku / Bahan Bacaan';

    protected static ?string $pluralModelLabel = 'Perpustakaan Sekolah';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('type', [MaterialType::Book->value, MaterialType::Reading->value]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Perpustakaan Sekolah')
                    ->description('Kelola koleksi Buku Digital dan Bahan Bacaan literasi umum yang dapat diakses oleh siswa dan guru.')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Kategori Koleksi')
                            ->options([
                                'book' => 'Buku Digital',
                                'reading' => 'Bahan Bacaan',
                            ])
                            ->default('book')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'reading') {
                                    $set('genre', array_key_first(Material::GENRES));
                                    $set('subject_id', Subject::where('slug', 'bahasa-indonesia')->value('id') ?? Subject::query()->value('id'));
                                } else {
                                    $set('genre', null);
                                    $set('subject_id', Subject::query()->value('id'));
                                }
                            }),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Buku / Bacaan')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Antologi Cerpen Nusantara / Buku Paket Matematika'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('subject_id')
                                    ->label('Mata Pelajaran')
                                    ->options(fn () => Subject::orderBy('name')->pluck('name', 'id'))
                                    ->visible(fn (Get $get): bool => ($get('type') ?? 'book') === 'book')
                                    ->required(fn (Get $get): bool => ($get('type') ?? 'book') === 'book')
                                    ->helperText('Buku Digital dapat dikelompokkan ke mata pelajaran yang sesuai.'),

                                Forms\Components\Select::make('genre')
                                    ->label('Genre Bacaan')
                                    ->options(Material::GENRES)
                                    ->default(array_key_first(Material::GENRES))
                                    ->visible(fn (Get $get): bool => ($get('type') ?? 'book') === 'reading')
                                    ->required(fn (Get $get): bool => ($get('type') ?? 'book') === 'reading')
                                    ->helperText('Pilih genre literasi untuk mengategorikan Bahan Bacaan ini.'),

                                Forms\Components\Select::make('target_grade')
                                    ->label('Kelas Tujuan')
                                    ->options(function () {
                                        $grades = Classroom::query()->whereNotNull('grade_level')->pluck('grade_level')->unique()->sort()->values();
                                        $options = [];
                                        foreach ($grades as $grade) {
                                            $options[$grade] = 'Kelas ' . $grade;
                                        }
                                        return $options;
                                    })
                                    ->placeholder('Semua Kelas (Tampilkan di Perpustakaan untuk Semua Siswa)')
                                    ->nullable()
                                    ->helperText('Biarkan kosong agar buku/bacaan tampil untuk seluruh siswa di perpustakaan.'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Sinopsis / Ringkasan Buku')
                            ->rows(3)
                            ->placeholder('Jelaskan cuplikan buku atau ringkasan bacaan secara singkat...'),
                    ]),

                Forms\Components\Section::make('File Dokumen PDF & Pratinjau')
                    ->description('Unggah dokumen PDF buku atau bahan bacaan.')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Unggah Dokumen PDF (Maks. 50 MB)')
                            ->disk('public')
                            ->directory('materials')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200)
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText('File format .pdf. Siswa dapat membaca langsung menggunakan PDF reader aplikasi.'),

                        Forms\Components\Placeholder::make('pdf_preview')
                            ->label('Pratinjau PDF')
                            ->content(function (Get $get, ?Material $record): HtmlString {
                                $pdfUrl = MaterialResource::resolvePreviewPdfUrl($get('file_path'), $record);

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
                    ]),

                Forms\Components\Section::make('Status Publikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                PublicationStatus::Published->value => 'Terbit (Dapat dibaca oleh siswa)',
                                PublicationStatus::Draft->value => 'Draf (Hanya guru & admin)',
                            ])
                            ->default(PublicationStatus::Published->value)
                            ->required(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraries::route('/'),
            'create' => Pages\CreateLibrary::route('/create'),
            'edit' => Pages\EditLibrary::route('/{record}/edit'),
        ];
    }
}
