@php
    /** @var \App\Models\Material $record */
    $type = $record->type instanceof \App\Enums\MaterialType ? $record->type->value : $record->type;
    $typeLabel = match ($type) {
        'book' => 'Buku Digital',
        'module' => 'Modul Pembelajaran',
        'reading' => 'Bahan Bacaan',
        'video' => 'Video Pembelajaran',
        'link' => 'Tautan Materi',
        default => ucfirst((string) $type),
    };
    $fileUrl = $record->file_path ? \Illuminate\Support\Facades\Storage::url($record->file_path) : null;
    $externalUrl = $record->url;
    $isUploadedVideo = $fileUrl && preg_match('/\.(mp4|webm|ogg|mov)(?:$|[?#])/i', $fileUrl);
    $isDirectVideoUrl = $externalUrl && preg_match('/\.(mp4|webm|ogg|mov)(?:$|[?#])/i', $externalUrl);

    $youtubeEmbedUrl = \App\Support\YouTube::embedUrl($externalUrl);
    $youtubeEmbeddable = $youtubeEmbedUrl ? \App\Support\YouTube::isEmbeddable($externalUrl) : true;
    $directVideoSrc = $isUploadedVideo ? $fileUrl : ($isDirectVideoUrl ? $externalUrl : null);
@endphp

<div class="space-y-4">
    <!-- Detail Koleksi -->
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-teal-700 dark:text-teal-400">
                    <span class="rounded-md bg-teal-100 px-2.5 py-0.5 font-bold text-teal-800 dark:bg-teal-900/50 dark:text-teal-300">
                        {{ $typeLabel }}
                    </span>
                    <span>•</span>
                    @if ($type === 'reading' && filled($record->genre))
                        <span>Genre: <strong>{{ $record->genre }}</strong></span>
                    @else
                        <span>{{ $record->subject?->name ?? 'Tanpa Mapel' }}</span>
                    @endif
                    <span>•</span>
                    <span>{{ $record->target_class_display }}</span>
                    <span>•</span>
                    <span>Diunggah oleh: <strong>{{ $record->creator?->name ?? 'Sistem' }}</strong></span>
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ $record->description ?: 'Tidak ada keterangan tambahan untuk koleksi ini.' }}
                </p>
            </div>

            @if ($fileUrl && ! $isUploadedVideo)
                <a
                    href="{{ $fileUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800"
                >
                    Buka PDF di Tab Baru ↗
                </a>
            @endif
        </div>
    </div>

    <!-- Area Pratinjau & Pemutar Dalam Aplikasi -->
    @if ($directVideoSrc)
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-black shadow-inner dark:border-gray-700">
            <video controls autoplay playsinline preload="metadata" class="max-h-[72vh] w-full bg-black">
                <source src="{{ $directVideoSrc }}">
                Browser Anda tidak mendukung pemutaran video langsung.
            </video>
        </div>
    @elseif ($youtubeEmbedUrl)
        @unless ($youtubeEmbeddable)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                ⚠️ Pemilik video ini <strong>menonaktifkan pemutaran di situs lain</strong> (biasanya video musik/berlisensi),
                sehingga YouTube menampilkan "Video ini tidak tersedia". Silakan ganti dengan video YouTube lain
                atau unggah file video <strong>.mp4</strong> agar dapat diputar di SUNDIGITAL.
            </div>
        @endunless
        <div class="aspect-video w-full overflow-hidden rounded-xl border border-gray-200 bg-black shadow-inner dark:border-gray-700">
            <iframe
                src="{{ $youtubeEmbedUrl }}"
                title="{{ $record->title }}"
                class="h-full w-full border-0"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
        </div>
    @elseif ($fileUrl)
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-[#525659] shadow-inner dark:border-gray-700">
            <iframe
                src="{{ $fileUrl }}#zoom=100"
                title="{{ $record->title }}"
                class="h-[72vh] w-full border-0 bg-white"
            ></iframe>
        </div>
    @elseif ($externalUrl)
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-300">Materi ini berupa tautan eksternal:</p>
            <a
                href="{{ $externalUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-3 inline-flex items-center gap-2 rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-800"
            >
                Buka Materi ({{ $externalUrl }}) ↗
            </a>
        </div>
    @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-center text-sm font-medium text-amber-800">
            Belum ada file dokumen atau video yang dilampirkan pada koleksi ini.
        </div>
    @endif
</div>
