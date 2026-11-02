<x-filament-panels::page>
    @php
        $user = auth()->user();
        $userId = (int) ($user?->id ?? 0);
        $isAdmin = $user && $user->isAdmin();

        $resolveTone = function (\App\Models\Material $material): string {
            $raw = $material->tone ?? $material->subject?->tone ?? null;
            $validTones = ['green', 'blue', 'orange', 'pink', 'lilac', 'yellow'];
            if ($raw && in_array($raw, $validTones, true)) {
                return $raw;
            }

            $typeVal = $material->type instanceof \App\Enums\MaterialType ? $material->type->value : (string) $material->type;
            if ($material->collection_category === 'general' || $typeVal === 'reading') {
                return 'lilac';
            }
            if ($typeVal === 'video' || $material->content_format === 'video') {
                return 'orange';
            }

            $palette = ['green', 'blue', 'orange', 'pink', 'lilac', 'yellow'];
            return $palette[((int) $material->subject_id ?: (int) $material->id) % count($palette)];
        };

        $resolveTypeLabel = function (\App\Models\Material $material): string {
            $val = $material->type instanceof \App\Enums\MaterialType ? $material->type->value : (string) $material->type;
            return match ($val) {
                'book' => 'Buku digital',
                'reading' => 'Bacaan umum',
                'module' => 'Modul',
                'video' => 'Video',
                default => 'Koleksi',
            };
        };

        $hasActiveFilters = filled($this->searchQuery) || filled($this->subjectFilter) || filled($this->genreFilter) || filled($this->gradeFilter);
    @endphp

    <div class="space-y-4">
        <!-- Navigasi Tab Kategori (Seragam dengan Katalog Siswa + Tab Video & Koleksi Saya) -->
        <div class="flex flex-wrap gap-2 border-b border-[#e4eae7] pb-3">
            <button
                type="button"
                wire:click="setCategory('learning')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all {{ $this->selectedCategory === 'learning' ? 'bg-[#0c7774] text-white shadow-sm' : 'border border-[#e4eae7] bg-white text-[#526764] hover:bg-[#f4f7f5]' }}"
            >
                <span>📚 Buku &amp; Modul Pelajaran</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $this->selectedCategory === 'learning' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]' }}">
                    {{ $counts['learning'] ?? 0 }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setCategory('general')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all {{ $this->selectedCategory === 'general' ? 'bg-[#0c7774] text-white shadow-sm' : 'border border-[#e4eae7] bg-white text-[#526764] hover:bg-[#f4f7f5]' }}"
            >
                <span>📖 Bacaan Umum</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $this->selectedCategory === 'general' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]' }}">
                    {{ $counts['general'] ?? 0 }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setCategory('video')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all {{ $this->selectedCategory === 'video' ? 'bg-[#0c7774] text-white shadow-sm' : 'border border-[#e4eae7] bg-white text-[#526764] hover:bg-[#f4f7f5]' }}"
            >
                <span>🎬 Video Pembelajaran</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $this->selectedCategory === 'video' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]' }}">
                    {{ $counts['video'] ?? 0 }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setCategory('mine')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all {{ $this->selectedCategory === 'mine' ? 'bg-[#0c7774] text-white shadow-sm' : 'border border-[#e4eae7] bg-white text-[#526764] hover:bg-[#f4f7f5]' }}"
            >
                <span>👤 {{ $isAdmin ? 'Unggahan Saya' : 'Koleksi Saya' }}</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $this->selectedCategory === 'mine' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]' }}">
                    {{ $counts['mine'] ?? 0 }}
                </span>
            </button>

            <button
                type="button"
                wire:click="setCategory('all')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all {{ $this->selectedCategory === 'all' ? 'bg-[#0c7774] text-white shadow-sm' : 'border border-[#e4eae7] bg-white text-[#526764] hover:bg-[#f4f7f5]' }}"
            >
                <span>Semua Koleksi</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold {{ $this->selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]' }}">
                    {{ $counts['all'] ?? 0 }}
                </span>
            </button>
        </div>

        <!-- Bar Pencarian (Identik dengan Katalog Siswa) -->
        <div class="flex flex-col gap-2 rounded-xl border border-[#e4eae7] bg-white p-3 sm:flex-row sm:items-center sm:gap-3">
            <label for="catalog-search" class="shrink-0 text-[11px] font-bold text-[#193b3a]">Cari koleksi</label>
            <input
                id="catalog-search"
                type="search"
                wire:model.live.debounce.300ms="searchQuery"
                placeholder="{{ $this->selectedCategory === 'learning' ? 'Cari judul materi, modul, mata pelajaran...' : ($this->selectedCategory === 'general' ? 'Cari judul buku, novel, cerpen, penulis...' : ($this->selectedCategory === 'video' ? 'Cari judul video pembelajaran, topik, atau pelajaran...' : 'Cari judul buku, materi, video, penulis, atau pelajaran...')) }}"
                class="min-h-9 min-w-0 flex-1 rounded-lg border-0 bg-[#f4f7f5] px-3 text-xs text-[#193b3a] placeholder:text-[#92a19c] focus:ring-2 focus:ring-[#0c7774]"
            />
            @if (filled($this->searchQuery))
                <button
                    type="button"
                    wire:click="$set('searchQuery', '')"
                    class="min-h-9 rounded-lg border border-[#e4eae7] bg-[#f8faf7] px-3 text-xs font-bold text-[#526764] hover:bg-[#edf1ee]"
                >
                    Bersihkan
                </button>
            @endif
        </div>

        <!-- Bar Filter Dinamis -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                @if ($this->selectedCategory !== 'general')
                    <div class="flex items-center gap-2">
                        <label for="subject-filter" class="text-[11px] font-bold text-[#193b3a]">Mata pelajaran:</label>
                        <select
                            id="subject-filter"
                            wire:model.live="subjectFilter"
                            class="min-h-9 rounded-lg border border-[#dce6e1] bg-white py-1 pl-3 pr-8 text-xs text-[#193b3a] focus:border-[#0c7774] focus:ring-[#0c7774]"
                        >
                            <option value="">Semua mata pelajaran</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($this->selectedCategory === 'general' || $this->selectedCategory === 'all' || $this->selectedCategory === 'mine')
                    <div class="flex items-center gap-2">
                        <label for="genre-filter" class="text-[11px] font-bold text-[#193b3a]">Genre:</label>
                        <select
                            id="genre-filter"
                            wire:model.live="genreFilter"
                            class="min-h-9 rounded-lg border border-[#dce6e1] bg-white py-1 pl-3 pr-8 text-xs text-[#193b3a] focus:border-[#0c7774] focus:ring-[#0c7774]"
                        >
                            <option value="">Semua genre bacaan</option>
                            @foreach ($genres as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($this->selectedCategory !== 'general')
                    <div class="flex items-center gap-2">
                        <label for="grade-filter" class="text-[11px] font-bold text-[#193b3a]">Kelas tujuan:</label>
                        <select
                            id="grade-filter"
                            wire:model.live="gradeFilter"
                            class="min-h-9 rounded-lg border border-[#dce6e1] bg-white py-1 pl-3 pr-8 text-xs text-[#193b3a] focus:border-[#0c7774] focus:ring-[#0c7774]"
                        >
                            <option value="">Semua tingkat kelas</option>
                            <option value="all">Semua Kelas (Umum)</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade }}">Kelas {{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($hasActiveFilters)
                    <button
                        type="button"
                        wire:click="resetCardFilters"
                        class="min-h-9 rounded-lg border border-[#e4eae7] bg-[#f8faf7] px-3 text-xs font-bold text-[#526764] hover:bg-[#edf1ee]"
                    >
                        ✕ Reset filter
                    </button>
                @endif
            </div>

            <p class="text-[11px] font-bold text-[#193b3a]">
                Menampilkan {{ $materials->count() }} koleksi
                @if ($this->selectedCategory === 'learning')
                    <span>(Buku &amp; Modul Pelajaran)</span>
                @elseif ($this->selectedCategory === 'general')
                    <span>(Bacaan Umum)</span>
                @elseif ($this->selectedCategory === 'video')
                    <span>(Video Pembelajaran)</span>
                @elseif ($this->selectedCategory === 'mine')
                    <span>(Koleksi Saya)</span>
                @endif
            </p>
        </div>

        <!-- Grid Kartu Koleksi Terpadu (Seragam dengan LibraryMaterialCard Siswa) -->
        @if ($materials->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#cfdcd6] bg-white p-10 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#e9f7f1] text-xl text-[#0c7774]">
                    ▤
                </div>
                <h3 class="text-sm font-extrabold text-[#193b3a]">
                    @if ($this->selectedCategory === 'mine')
                        Belum ada koleksi yang Anda unggah
                    @elseif ($this->selectedCategory === 'general')
                        Bacaan umum belum tersedia
                    @elseif ($this->selectedCategory === 'video')
                        Video pembelajaran belum tersedia
                    @else
                        Koleksi belum ditemukan
                    @endif
                </h3>
                <p class="mx-auto mt-1 max-w-md text-xs leading-relaxed text-[#667b79]">
                    @if ($hasActiveFilters)
                        Tidak ada koleksi yang cocok dengan filter atau kata kunci pencarian Anda. Silakan coba kata kunci lain atau reset filter.
                    @else
                        Klik tombol <strong>+ Tambah koleksi</strong> di kanan atas untuk mengunggah buku pelajaran, modul PDF, bacaan umum, atau video pembelajaran baru.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($materials as $material)
                    @php
                        $typeVal = $material->type instanceof \App\Enums\MaterialType ? $material->type->value : (string) $material->type;
                        $isGeneral = $material->collection_category === 'general' || $typeVal === 'reading';
                        $isVideo = $typeVal === 'video' || $material->content_format === 'video';
                        $tone = $resolveTone($material);
                        $coverLabel = $material->cover_text ?: ($isGeneral ? ($material->genre ?: 'Bacaan Umum') : ($material->subject?->name ?? 'Materi Umum'));

                        $statusVal = $material->status instanceof \App\Enums\PublicationStatus ? $material->status->value : (string) $material->status;
                        $isPublished = $statusVal === 'published';
                        $isMine = (int) $material->created_by === $userId;
                        $canManage = $isAdmin || $isMine || ($user && $user->can('update', $material));
                        $canDelete = $isAdmin || $isMine || ($user && $user->can('delete', $material));

                        $fileUrl = $material->file_path ? \Illuminate\Support\Facades\Storage::url($material->file_path) : null;
                        $targetUrl = $fileUrl ?: $material->url;
                        $editUrl = $resourceClass::getUrl('edit', ['record' => $material]);
                        $quizCount = $material->quizzes?->count() ?? 0;
                    @endphp

                    <article
                        wire:key="catalog-material-{{ $material->id }}"
                        class="flex min-h-[320px] flex-col rounded-2xl border border-[#e4eae7] bg-white p-3 transition duration-150 hover:border-[#b7d3c8] hover:shadow-sm"
                    >
                        <!-- Banner Sampul Visual (Identik dengan Kartu Siswa) -->
                        <div class="relative flex h-32 shrink-0 flex-col justify-between overflow-hidden rounded-xl p-4 subject-tone-{{ $tone }}">
                            <div class="pointer-events-none absolute -right-4 -top-8 h-28 w-28 rounded-full border-[14px] border-white/25"></div>

                            <!-- Baris Atas Sampul: Penanda Milik Saya & Status Terbit/Draf -->
                            <div class="relative z-10 flex items-center justify-between gap-2">
                                @if ($isMine)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/85 px-2.5 py-0.5 text-[10px] font-extrabold text-[#0c7774] shadow-2xs backdrop-blur-xs">
                                        👤 Koleksi Saya
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold shadow-2xs backdrop-blur-xs {{ $isPublished ? 'bg-white/85 text-[#176356]' : 'bg-amber-100/95 text-amber-900' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $isPublished ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ $isPublished ? 'Terbit' : 'Draf' }}
                                </span>
                            </div>

                            <!-- Judul Kategori/Sampul -->
                            <span class="relative z-10 line-clamp-2 text-lg font-black leading-tight">
                                {{ $coverLabel }}
                            </span>
                        </div>

                        <!-- Baris Badge Kategori & Sub-info -->
                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            @if ($isGeneral)
                                <span class="rounded-md bg-[#f3e8ff] px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#6b21a8]">
                                    📖 Bacaan Umum
                                </span>
                            @elseif ($isVideo)
                                <span class="rounded-md bg-[#ffe4e6] px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#be123c]">
                                    🎬 Video Pembelajaran
                                </span>
                            @else
                                <span class="rounded-md bg-[#e0f2fe] px-1.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-[#0369a1]">
                                    📚 Buku Pelajaran
                                </span>
                            @endif

                            @if ($isGeneral && filled($material->author))
                                <span class="text-[10px] font-medium text-[#526764]">
                                    Karya {{ $material->author }}
                                </span>
                            @else
                                <span class="text-[10px] font-medium text-[#526764]">
                                    {{ $material->category_display }} · {{ $resolveTypeLabel($material) }}
                                </span>
                            @endif
                        </div>

                        <!-- Judul & Deskripsi Koleksi -->
                        <h2 class="mt-2 line-clamp-2 text-[13.5px] font-extrabold leading-5 text-[#193b3a]">
                            {{ $material->title }}
                        </h2>

                        <p class="mt-1.5 line-clamp-2 text-[11px] leading-relaxed text-[#667b79]">
                            {{ $material->description ?: 'Koleksi perpustakaan & materi pembelajaran digital sekolah.' }}
                        </p>

                        <!-- Baris Info Kelas Tujuan, Pengunggah & Kuis Terkait -->
                        <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-[10px] text-[#526764]">
                            <span class="inline-flex items-center gap-1 rounded-md bg-[#f4f7f5] px-2 py-0.5 font-bold text-[#395754]">
                                🏫 {{ $material->target_class_display }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-md bg-[#f4f7f5] px-2 py-0.5 font-medium text-[#526764]">
                                Oleh: {{ $isMine ? 'Anda (' . ($material->creator?->name ?? $user?->name) . ')' : ($material->creator?->name ?? 'Admin Sekolah') }}
                            </span>
                            @if ($quizCount > 0)
                                <span class="inline-flex items-center rounded-full bg-[#fff4df] px-2 py-0.5 text-[10px] font-bold text-[#76531b]">
                                    ✳ {{ $quizCount }} kuis terkait
                                </span>
                            @endif
                        </div>

                        <!-- Footer Aksi Kartu (Gaya Katalog Siswa + Tombol Kelola untuk Pemilik/Admin) -->
                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-[#edf1ee] pt-3">
                            <button
                                type="button"
                                wire:click="openPreview({{ $material->id }})"
                                class="inline-flex min-h-8 items-center rounded-lg bg-[#e9f7f1] px-3 text-[11px] font-extrabold text-[#075f5d] transition hover:bg-[#d7eeea]"
                            >
                                {{ $isVideo ? 'Putar video →' : 'Baca sekarang →' }}
                            </button>

                            <div class="flex items-center gap-1.5">
                                @if ($targetUrl && ! $isVideo)
                                    <a
                                        href="{{ $targetUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#e4eae7] text-xs font-bold text-[#0c7774] transition hover:bg-[#e9f7f1]"
                                        title="Buka dokumen di tab baru"
                                    >
                                        ↗
                                    </a>
                                @endif

                                @if ($canManage)
                                    <a
                                        href="{{ $editUrl }}"
                                        class="inline-flex h-8 items-center gap-1 rounded-lg border border-[#e4eae7] bg-white px-2.5 text-[11px] font-bold text-[#193b3a] transition hover:border-[#0c7774] hover:bg-[#f4f7f5] hover:text-[#0c7774]"
                                        title="Ubah koleksi ini"
                                    >
                                        ✏️ Ubah
                                    </a>
                                @endif

                                @if ($canDelete)
                                    <button
                                        type="button"
                                        wire:click="deleteMaterial({{ $material->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus koleksi '{{ addslashes($material->title) }}'?"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#eed8d5] bg-[#fffaf8] text-xs text-[#a53232] transition hover:bg-[#fff0ed]"
                                        title="Hapus koleksi ini"
                                    >
                                        🗑️
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Pembaca PDF / Pemutar Video Dalam Aplikasi -->
    @if ($previewRecord)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 p-4 backdrop-blur-xs"
            wire:click.self="closePreview"
        >
            <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 border-b border-[#e4eae7] px-6 py-4">
                    <div>
                        <h3 class="text-base font-extrabold text-[#193b3a]">
                            {{ $previewRecord->title }}
                        </h3>
                        <p class="text-xs text-[#667b79]">
                            {{ $previewRecord->category_display }} • {{ $previewRecord->target_class_display }} • Diunggah oleh {{ $previewRecord->creator?->name ?? 'Admin Sekolah' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closePreview"
                        class="inline-flex items-center gap-1 rounded-xl border border-[#e4eae7] bg-white px-3 py-1.5 text-xs font-bold text-[#193b3a] transition hover:bg-[#f4f7f5]"
                    >
                        ✕ Tutup
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    @include('filament.components.material-modal-preview', ['record' => $previewRecord])
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
