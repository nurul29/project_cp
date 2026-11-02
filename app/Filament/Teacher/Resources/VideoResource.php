<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\MaterialType;
use App\Enums\PublicationStatus;
use App\Enums\VideoProvider;
use App\Filament\Teacher\Resources\VideoResource\Pages;
use App\Models\Material;
use App\Models\Subject;
use App\Support\YouTube;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class VideoResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Video Pembelajaran';

    protected static ?string $modelLabel = 'Video Pembelajaran';

    protected static ?string $pluralModelLabel = 'Video Pembelajaran';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', MaterialType::Video->value);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Video Pembelajaran')
                    ->description('Tambahkan media video pembelajaran dari YouTube atau unggah file video langsung.')
                    ->schema([
                        Forms\Components\Hidden::make('type')
                            ->default(MaterialType::Video->value),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Video Pembelajaran')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Animasi Pembuktian Teorema Pythagoras'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('subject_id')
                                    ->label('Mata Pelajaran')
                                    ->options(fn () => MaterialResource::getSubjectOptionsForUser('video'))
                                    ->default(fn () => array_key_first(MaterialResource::getSubjectOptionsForUser('video')))
                                    ->required()
                                    ->helperText('Otomatis menampilkan mata pelajaran yang Anda ampu.'),

                                Forms\Components\Select::make('target_grade')
                                    ->label('Kelas Tujuan')
                                    ->options(fn (Get $get) => MaterialResource::getTargetGradeOptionsForUser('video', $get('subject_id')))
                                    ->placeholder('Semua Kelas (Tampilkan di Semua Kelas)')
                                    ->nullable()
                                    ->helperText('Pilih tingkat kelas yang Anda ampu (mis. Kelas VII / Kelas VIII), atau semua kelas.'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Ringkasan / Catatan Video')
                            ->rows(3)
                            ->placeholder('Jelaskan konsep yang dibahas di dalam video ini...'),
                    ]),

                Forms\Components\Section::make('Sumber Video & Pratinjau')
                    ->description('Pilih salah satu: tautan YouTube yang dapat disematkan atau unggah file video (.mp4).')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Tautan Video YouTube')
                            ->url()
                            ->live(debounce: 500)
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if ($value && ! YouTube::isEmbeddable($value)) {
                                        $fail('Pemilik video YouTube ini menonaktifkan pemutaran di situs lain. Gunakan video lain atau unggah file .mp4.');
                                    }
                                },
                            ])
                            ->helperText('Tempel tautan video YouTube di sini. Pratinjau akan otomatis muncul di bawah.'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Atau Unggah File Video (.mp4 / .webm — Maks. 50 MB)')
                            ->disk('public')
                            ->directory('materials')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                            ->maxSize(51200)
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->helperText('Gunakan opsi ini jika video tidak tersedia di YouTube atau memiliki hak cipta embedding.'),

                        Forms\Components\Placeholder::make('video_preview')
                            ->label('Pratinjau Video')
                            ->content(function (Get $get, ?Material $record): HtmlString {
                                $url = $get('url') ?: $record?->url;
                                $filePath = $get('file_path') ?: $record?->file_path;
                                $videoFileUrl = MaterialResource::resolvePreviewPdfUrl($filePath, $record);

                                if ($videoFileUrl && preg_match('/\.(mp4|webm|ogg|mov)(?:$|[?#])/i', $videoFileUrl)) {
                                    return new HtmlString('
                                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-black shadow-inner">
                                            <video controls playsinline class="max-h-80 w-full bg-black">
                                                <source src="'.$videoFileUrl.'">
                                            </video>
                                        </div>
                                    ');
                                }

                                if ($url) {
                                    $embedUrl = YouTube::embedUrl($url);
                                    if ($embedUrl) {
                                        return new HtmlString('
                                            <div class="aspect-video w-full overflow-hidden rounded-xl border border-gray-200 bg-black shadow-inner">
                                                <iframe src="'.$embedUrl.'" class="h-full w-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                            </div>
                                        ');
                                    }
                                }

                                return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-xs text-gray-500">Masukkan tautan video YouTube atau unggah file video untuk melihat pratinjau.</div>');
                            }),
                    ]),

                Forms\Components\Section::make('Status Publikasi')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                PublicationStatus::Published->value => 'Terbit (Dapat diakses oleh siswa)',
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
