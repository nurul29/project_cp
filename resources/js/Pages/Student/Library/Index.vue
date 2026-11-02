<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LibraryMaterialCard from '@/Components/LibraryMaterialCard.vue';

const props = defineProps({
    materials: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    genres: { type: Array, default: () => [] },
    selectedCategory: { type: String, default: 'all' },
    selectedSubject: { type: String, default: null },
    selectedGenre: { type: String, default: null },
    search: { type: String, default: '' },
    counts: { type: Object, default: () => ({ all: 0, learning: 0, general: 0 }) },
    savedMaterialIds: { type: Array, default: () => [] },
});

const search = ref(props.search);
watch(() => props.search, (value) => { search.value = value; });

const hasActiveFilters = computed(() => {
    return Boolean(props.selectedSubject || props.selectedGenre || props.search);
});

const setCategory = (category) => {
    router.get(route('siswa.catalog.index'), {
        ...(category !== 'all' ? { category } : {}),
        ...(search.value ? { q: search.value } : {}),
    }, { preserveState: true, preserveScroll: true });
};

const changeSubject = (event) => {
    const subject = event.target.value;
    router.get(route('siswa.catalog.index'), {
        ...(props.selectedCategory !== 'all' ? { category: props.selectedCategory } : {}),
        ...(subject ? { subject } : {}),
        ...(search.value ? { q: search.value } : {}),
    }, { preserveState: true, preserveScroll: true });
};

const changeGenre = (event) => {
    const genre = event.target.value;
    router.get(route('siswa.catalog.index'), {
        ...(props.selectedCategory !== 'all' ? { category: props.selectedCategory } : {}),
        ...(genre ? { genre } : {}),
        ...(search.value ? { q: search.value } : {}),
    }, { preserveState: true, preserveScroll: true });
};

const submitSearch = () => {
    router.get(route('siswa.catalog.index'), {
        ...(props.selectedCategory !== 'all' ? { category: props.selectedCategory } : {}),
        ...(props.selectedSubject ? { subject: props.selectedSubject } : {}),
        ...(props.selectedGenre ? { genre: props.selectedGenre } : {}),
        ...(search.value ? { q: search.value } : {}),
    }, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
    search.value = '';
    router.get(route('siswa.catalog.index'), {
        ...(props.selectedCategory !== 'all' ? { category: props.selectedCategory } : {}),
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Katalog buku" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-[944px]">
            <header class="mb-5">
                <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-[#0c7774]">Ruang siswa</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#193b3a] sm:text-3xl">Katalog perpustakaan</h1>
                <p class="mt-2 text-xs leading-5 text-[#667b79]">Jelajahi buku pelajaran, modul, dan buku bacaan umum dalam format PDF.</p>
            </header>

            <!-- Navigasi Tab Pemisah Kategori -->
            <div class="mb-4 flex flex-wrap gap-2 border-b border-[#e4eae7] pb-3">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all"
                    :class="selectedCategory === 'learning' ? 'bg-[#0c7774] text-white shadow-sm' : 'bg-white text-[#526764] hover:bg-[#f4f7f5] border border-[#e4eae7]'"
                    @click="setCategory('learning')"
                >
                    <span>📚 Buku &amp; Modul Pelajaran</span>
                    <span
                        class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold"
                        :class="selectedCategory === 'learning' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]'"
                    >
                        {{ counts.learning ?? 0 }}
                    </span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all"
                    :class="selectedCategory === 'general' ? 'bg-[#0c7774] text-white shadow-sm' : 'bg-white text-[#526764] hover:bg-[#f4f7f5] border border-[#e4eae7]'"
                    @click="setCategory('general')"
                >
                    <span>📖 Bacaan Umum</span>
                    <span
                        class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold"
                        :class="selectedCategory === 'general' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]'"
                    >
                        {{ counts.general ?? 0 }}
                    </span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all"
                    :class="selectedCategory === 'all' ? 'bg-[#0c7774] text-white shadow-sm' : 'bg-white text-[#526764] hover:bg-[#f4f7f5] border border-[#e4eae7]'"
                    @click="setCategory('all')"
                >
                    <span>Semua Koleksi</span>
                    <span
                        class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold"
                        :class="selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-[#e4eae7] text-[#193b3a]'"
                    >
                        {{ counts.all ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Form Pencarian -->
            <form class="mb-3 flex flex-col gap-2 rounded-xl border border-[#e4eae7] bg-white p-3 sm:flex-row sm:items-center sm:gap-3" @submit.prevent="submitSearch">
                <label for="library-search" class="shrink-0 text-[11px] font-bold text-[#193b3a]">Cari koleksi</label>
                <input
                    id="library-search"
                    v-model="search"
                    type="search"
                    :placeholder="selectedCategory === 'learning' ? 'Cari judul materi, modul, mata pelajaran...' : (selectedCategory === 'general' ? 'Cari judul buku, novel, cerpen, penulis...' : 'Cari judul buku, materi, penulis, atau pelajaran...')"
                    class="min-h-9 min-w-0 flex-1 rounded-lg border-0 bg-[#f4f7f5] px-3 text-xs text-[#193b3a] placeholder:text-[#92a19c] focus:ring-2 focus:ring-[#0c7774]"
                />
                <button type="submit" class="min-h-9 rounded-lg bg-[#0c7774] px-4 text-xs font-bold text-white hover:bg-[#075f5d]">Cari</button>
            </form>

            <!-- Bar Filter Dinamis -->
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Filter Mata Pelajaran (khusus Buku Pelajaran atau Semua) -->
                    <div v-if="selectedCategory !== 'general'" class="flex items-center gap-2">
                        <label for="subject-filter" class="text-[11px] font-bold text-[#193b3a]">Mata pelajaran:</label>
                        <select
                            id="subject-filter"
                            class="min-h-9 rounded-lg border-[#dce6e1] bg-white py-1 pl-3 pr-8 text-xs text-[#193b3a] focus:border-[#0c7774] focus:ring-[#0c7774]"
                            :value="selectedSubject || ''"
                            @change="changeSubject"
                        >
                            <option value="">Semua mata pelajaran</option>
                            <option v-for="subject in subjects" :key="subject.slug" :value="subject.slug">{{ subject.name }}</option>
                        </select>
                    </div>

                    <!-- Filter Genre (khusus Bacaan Umum atau Semua) -->
                    <div v-if="selectedCategory !== 'learning'" class="flex items-center gap-2">
                        <label for="genre-filter" class="text-[11px] font-bold text-[#193b3a]">Genre:</label>
                        <select
                            id="genre-filter"
                            class="min-h-9 rounded-lg border-[#dce6e1] bg-white py-1 pl-3 pr-8 text-xs text-[#193b3a] focus:border-[#0c7774] focus:ring-[#0c7774]"
                            :value="selectedGenre || ''"
                            @change="changeGenre"
                        >
                            <option value="">Semua genre bacaan</option>
                            <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                        </select>
                    </div>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="min-h-9 rounded-lg border border-[#e4eae7] bg-[#f8faf7] px-3 text-xs font-bold text-[#526764] hover:bg-[#edf1ee]"
                        @click="resetFilters"
                    >
                        ✕ Reset filter
                    </button>
                </div>

                <p class="text-[11px] font-bold text-[#193b3a]">
                    Menampilkan {{ materials.length }} koleksi
                    <span v-if="selectedCategory === 'learning'">(Buku Pelajaran)</span>
                    <span v-else-if="selectedCategory === 'general'">(Bacaan Umum)</span>
                </p>
            </div>

            <!-- Grid Kartu Koleksi -->
            <div v-if="materials.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <LibraryMaterialCard
                    v-for="material in materials"
                    :key="material.id"
                    :material="material"
                    :saved="savedMaterialIds.includes(material.id)"
                />
            </div>

            <!-- Empty State -->
            <EmptyState
                v-else
                :title="selectedCategory === 'learning' ? 'Buku pelajaran belum tersedia' : (selectedCategory === 'general' ? 'Bacaan umum belum tersedia' : 'Koleksi belum ditemukan')"
                :description="hasActiveFilters ? 'Tidak ada koleksi yang cocok dengan filter atau kata kunci pencarian Anda. Silakan coba kata kunci lain atau reset filter.' : 'Belum ada koleksi yang dibagikan pada kategori ini.'"
                icon="▤"
            />
        </div>
    </AuthenticatedLayout>
</template>
