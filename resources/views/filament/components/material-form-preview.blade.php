@php
    $youtubeEmbedUrl = \App\Support\YouTube::embedUrl($videoUrl ?? null);
    $youtubeEmbeddable = $youtubeEmbedUrl ? \App\Support\YouTube::isEmbeddable($videoUrl) : true;
@endphp

<div
    x-data="{
        blobUrl: null,
        blobType: null,
        fileName: null,
        useEmbedFallback: false,
        init() {
            const handleFile = (file) => {
                if (!file) return;
                const name = (file.name || '').toLowerCase();
                const isPdf = file.type === 'application/pdf' || name.endsWith('.pdf');
                const isVideo = (file.type && file.type.startsWith('video/')) || /\.(mp4|webm|ogg|mov)$/.test(name);
                if (isPdf || isVideo) {
                    if (this.blobUrl) URL.revokeObjectURL(this.blobUrl);
                    this.blobUrl = URL.createObjectURL(file);
                    this.blobType = isVideo ? 'video' : 'pdf';
                    this.fileName = file.name;
                }
            };

            document.addEventListener('change', (e) => {
                if (e.target && e.target.type === 'file' && e.target.files && e.target.files[0]) {
                    handleFile(e.target.files[0]);
                }
            }, true);

            document.addEventListener('FilePond:addfile', (e) => {
                if (e.detail && e.detail.file && e.detail.file.file) {
                    handleFile(e.detail.file.file);
                }
            }, true);

            document.addEventListener('FilePond:removefile', () => {
                if (this.blobUrl) URL.revokeObjectURL(this.blobUrl);
                this.blobUrl = null;
                this.blobType = null;
                this.fileName = null;
            }, true);
        }
    }"
    class="mt-2 space-y-3"
>
    @if (($type ?? 'book') !== 'video')
        <!-- Pratinjau PDF dari Server (TemporaryUpload / Saved File) atau Client Blob -->
        <template x-if="blobUrl || @js((bool) $pdfUrl)">
            <div class="overflow-hidden rounded-xl border border-teal-200 bg-teal-50/40 p-3 dark:border-teal-800 dark:bg-gray-800">
                <div class="mb-2.5 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-teal-800 dark:text-teal-300">
                        <span>👁️ Pratinjau Dokumen yang Akan Diunggah (100%)</span>
                        <span x-show="fileName" x-text="'• ' + fileName" class="font-medium text-gray-600 dark:text-gray-400"></span>
                    </div>
                    <a
                        :href="blobUrl || @js($pdfUrl)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800"
                    >
                        Buka Pratinjau Penuh di Tab Baru ↗
                    </a>
                </div>
                <div class="overflow-hidden rounded-lg border border-gray-300 bg-[#525659]">
                    <iframe
                        :src="(blobUrl || @js($pdfUrl)) + '#zoom=100'"
                        title="Pratinjau PDF"
                        class="h-[62vh] w-full border-0 bg-white"
                    ></iframe>
                </div>
            </div>
        </template>

        <template x-if="!blobUrl && !@js((bool) $pdfUrl)">
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                Pilih file PDF di atas untuk melihat pratinjau dokumen secara langsung sebelum disimpan.
            </div>
        </template>
    @else
        <!-- Pratinjau Video Pembelajaran (dari File Video Lokal atau Tautan YouTube/MP4) -->
        <template x-if="blobUrl && blobType === 'video'">
            <div class="overflow-hidden rounded-xl border border-teal-200 bg-teal-50/40 p-3 dark:border-teal-800 dark:bg-gray-800">
                <div class="mb-2 flex items-center justify-between text-xs font-bold text-teal-800 dark:text-teal-300">
                    <span>🎬 Pratinjau File Video yang Akan Diunggah</span>
                    <span x-show="fileName" x-text="fileName" class="font-medium text-gray-600 dark:text-gray-400"></span>
                </div>
                <video :src="blobUrl" controls playsinline class="max-h-[56vh] w-full rounded-lg bg-black"></video>
            </div>
        </template>

        @if ($youtubeEmbedUrl)
            <div x-show="!blobUrl" class="overflow-hidden rounded-xl border border-teal-200 bg-teal-50/40 p-3 dark:border-teal-800 dark:bg-gray-800">
                <div class="mb-2 text-xs font-bold text-teal-800 dark:text-teal-300">
                    🎬 Pratinjau Video (diputar di dalam SUNDIGITAL)
                </div>

                @unless ($youtubeEmbeddable)
                    <div class="mb-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                        ⚠️ Pemilik video ini menonaktifkan pemutaran di situs lain, sehingga tidak bisa diputar di SUNDIGITAL.
                        Gunakan video YouTube lain atau unggah file <strong>.mp4</strong>.
                    </div>
                @endunless

                <div class="aspect-video overflow-hidden rounded-lg border border-gray-300 bg-black">
                    <iframe
                        src="{{ $youtubeEmbedUrl }}"
                        title="Pratinjau Video"
                        class="h-full w-full border-0"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        @elseif (! empty($videoUrl) || ! empty($pdfUrl))
            @php
                $directMedia = (! empty($pdfUrl) && preg_match('/\.(mp4|webm|ogg|mov)(?:$|[?#])/i', $pdfUrl))
                    ? $pdfUrl
                    : $videoUrl;
            @endphp
            @if ($directMedia)
                <div x-show="!blobUrl" class="overflow-hidden rounded-xl border border-teal-200 bg-teal-50/40 p-3 dark:border-teal-800 dark:bg-gray-800">
                    <div class="mb-2 text-xs font-bold text-teal-800 dark:text-teal-300">
                        🎬 Pratinjau Video Pembelajaran
                    </div>
                    <video controls playsinline class="max-h-[56vh] w-full rounded-lg bg-black">
                        <source src="{{ $directMedia }}">
                    </video>
                </div>
            @endif
        @else
            <div x-show="!blobUrl" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-400">
                Masukkan tautan video atau unggah file video (.mp4) di atas untuk memutar pratinjau langsung di SUNDIGITAL.
            </div>
        @endif
    @endif
</div>
