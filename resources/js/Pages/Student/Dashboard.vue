<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LibraryMaterialCard from '@/Components/LibraryMaterialCard.vue';

const props = defineProps({
    subjectsCount: { type: Number, default: 0 },
    featuredMaterials: { type: Array, default: () => [] },
    recentReadings: { type: Array, default: () => [] },
    videoCount: { type: Number, default: 0 },
    activities: { type: Array, default: () => [] },
    subjects: { type: Array, default: () => [] },
    progress: {
        type: Object,
        default: () => ({ completed: 0, total: 0, percentage: 0 }),
    },
});

const page = usePage();
const search = ref('');
const submitSearch = () => router.get(route('siswa.catalog.index'), search.value ? { q: search.value } : {});

const firstName = computed(() => {
    const fullName = page.props.auth.user?.name || '';
    return fullName.split(' ')[0] || 'Siswa';
});

// Dynamic Hero reading based on recent reading or featured material
const heroItem = computed(() => {
    if (props.recentReadings.length > 0) {
        return props.recentReadings[0];
    }
    if (props.featuredMaterials.length > 0) {
        return props.featuredMaterials[0];
    }
    return null;
});

const heroSubject = computed(() => heroItem.value?.subject || 'MATEMATIKA');
const heroTitle = computed(() => heroItem.value?.title || 'Teorema Pythagoras');
const heroDesc = computed(() => heroItem.value?.description || 'Pelajari hubungan sisi-sisi segitiga siku-siku lewat bacaan dan latihan singkat.');
const heroHref = computed(() => heroItem.value?.href || (heroItem.value?.id ? route('siswa.library.show', heroItem.value.id) : route('siswa.catalog.index')));

// Modules for "Ruang belajarmu" aligned with active student features (Digital Library & Multiple-Choice Quizzes)
const learningModules = [
    { page: 'perpustakaan', icon: '📚', name: 'Katalog buku', desc: 'Buku pelajaran & umum', href: route('siswa.catalog.index') },
    { page: 'mapel', icon: '🗂️', name: 'Kategori pelajaran', desc: 'Materi tiap mapel', href: route('siswa.subjects.index') },
    { page: 'video', icon: '🎬', name: 'Video pendukung', desc: 'Tonton penjelasan', href: route('siswa.videos.index') },
    { page: 'kuis', icon: '🧩', name: 'Latihan & kuis', desc: 'Kuis pilihan ganda', href: route('siswa.quizzes.index') },
    { page: 'nilai', icon: '🏅', name: 'Nilai saya', desc: 'Skor & hasil kuis', href: route('siswa.grades.index') },
    { page: 'rak', icon: '❤️', name: 'Rak saya', desc: 'Koleksi tersimpan', href: route('siswa.shelf.index') },
];

const subjectToneClass = (tone) => {
    const map = {
        green: 'green',
        blue: 'blue',
        orange: 'orange',
        pink: 'pink',
        lilac: 'lilac',
        yellow: 'yellow',
    };
    return map[tone] || 'green';
};
</script>

<template>
    <Head title="Beranda Ruang Siswa" />
    <AuthenticatedLayout>
        <!-- Page Header according to Mockup -->
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang siswa</div>
                <h1>Halo, {{ firstName }}! 👋</h1>
                <p>Pilih mata pelajaran dan lanjutkan aktivitas belajarmu.</p>
            </div>
        </div>

        <!-- Student Hero Banner according to Mockup -->
        <section class="student-hero">
            <div class="copy">
                <div class="mini">SUNDIGITAL • SMP NEGERI 16 DEPOK</div>
                <h2>Satu perpustakaan,<br>banyak pengetahuan.</h2>
                <p>Jelajahi buku dan bacaan lintas mata pelajaran. Simpan pilihanmu, lalu baca kapan saja.</p>
                <Link :href="heroHref" class="lightbtn">
                    {{ heroItem ? 'Lanjutkan membaca →' : 'Jelajahi katalog buku →' }}
                </Link>
            </div>
            <div class="hero-illustration" aria-hidden="true">
                <svg width="145" height="160" viewBox="0 0 145 160" fill="none">
                    <rect x="9" y="33" width="42" height="111" rx="6" fill="#E8AB67"/>
                    <path d="M18 47H40M18 53H36M18 128H39" stroke="#81501F" stroke-width="3"/>
                    <rect x="57" y="9" width="43" height="135" rx="6" fill="#D7ECDC"/>
                    <path d="M66 28H91M66 35H85M66 128H90" stroke="#195B55" stroke-width="3"/>
                    <rect x="106" y="49" width="31" height="95" rx="6" fill="#F8D6C8"/>
                    <path d="M114 64H129M114 70H125M114 128H129" stroke="#844D4D" stroke-width="3"/>
                </svg>
            </div>
        </section>

        <!-- Search Bar matching Mockup -->
        <form class="search-bar" @submit.prevent="submitSearch">
            <label for="dashboard-library-search">Cari koleksi</label>
            <input
                id="dashboard-library-search"
                v-model="search"
                type="search"
                placeholder="Judul buku, modul, atau topik materi..."
            />
            <button type="submit" class="primary">
                Cari materi
            </button>
        </form>

        <!-- Overview Grid: Kuis Terdekat & Progres Belajar -->
        <div class="overview">
            <section class="panel">
                <div class="panel-head">
                    <h2>Kuis &amp; latihan aktif</h2>
                    <Link :href="route('siswa.quizzes.index')" class="text-link">
                        Lihat semua →
                    </Link>
                </div>
                <div v-if="activities.length" class="space-y-0.5">
                    <div
                        v-for="(activity, index) in activities"
                        :key="`${activity.type}-${index}`"
                        class="lesson-line"
                    >
                        <span
                            class="tile-icon"
                            :class="activity.type === 'Kuis' ? 'blue' : 'peach'"
                        >
                            {{ activity.icon || '🧩' }}
                        </span>
                        <div>
                            <strong>{{ activity.title }}</strong>
                            <small>{{ activity.subject }} • {{ activity.questionCount ? `${activity.questionCount} soal pilihan ganda` : activity.type }}</small>
                        </div>
                        <Link :href="activity.href">
                            Mulai kuis
                        </Link>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="Semua kuis telah diselesaikan"
                    description="Belum ada kuis pilihan ganda baru yang perlu kamu kerjakan saat ini."
                    icon="✓"
                />
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Progres kuis belajarmu</h2>
                    <small>Semester berjalan</small>
                </div>
                <div style="font-size: 29px; font-weight: 800; letter-spacing: -0.05em; margin-top: 19px; color: #193b3a;">
                    {{ progress.completed }} dari {{ progress.total }}
                </div>
                <div style="font-size: 12px; color: #667b79;">
                    kuis pilihan ganda telah diselesaikan
                </div>
                <div class="progress-track" role="progressbar" :aria-valuenow="progress.percentage" aria-valuemin="0" aria-valuemax="100" :aria-label="`Progres belajar ${progress.percentage}%`">
                    <div class="progress-fill" :style="{ width: `${progress.percentage}%` }"></div>
                </div>
                <div class="progress-foot">
                    <span>Capaian pengerjaan</span>
                    <b>{{ progress.percentage }}%</b>
                </div>
            </section>
        </div>

        <!-- Jelajahi Mata Pelajaran -->
        <div class="section-head">
            <h2>Jelajahi mata pelajaran</h2>
            <Link :href="route('siswa.subjects.index')" class="text-link">
                Lihat semua →
            </Link>
        </div>
        <div v-if="subjects.length" class="subject-grid">
            <Link
                v-for="(s, index) in subjects.slice(0, 4)"
                :key="s.id"
                :href="route('siswa.catalog.index', { subject: s.slug })"
                class="subject-card"
            >
                <span class="subject-symbol" :class="subjectToneClass(s.tone)" aria-hidden="true">
                    {{ s.icon || (s.name === 'Matematika' ? 'Σ' : s.name.slice(0, 2)) }}
                </span>
                <strong>{{ s.name }}</strong>
                <small>{{ s.description || 'Koleksi materi pembelajaran' }}</small>
                <span class="subject-arrow">Buka materi →</span>
            </Link>
        </div>

        <!-- Ruang Belajarmu Modules Grid according to Mockup -->
        <div class="section-head">
            <h2>Ruang belajarmu</h2>
        </div>
        <div class="module-grid">
            <Link
                v-for="(mod, j) in learningModules"
                :key="mod.page"
                :href="mod.href"
                class="module-card"
            >
                <span class="tile-icon" :class="j % 3 === 1 ? 'peach' : j % 3 === 2 ? 'blue' : ''">
                    {{ mod.icon }}
                </span>
                <span>
                    <strong>{{ mod.name }}</strong>
                    <small>{{ mod.desc }}</small>
                </span>
            </Link>
        </div>

        <!-- Support Banner: Latihan setelah membaca -->
        <section class="library-support qz-home-support">
            <div>
                <h2>Latihan setelah membaca</h2>
                <p>Uji pemahaman setelah membaca modul atau pilih kuis mandiri untuk kelasmu.</p>
            </div>
            <Link :href="route('siswa.quizzes.index')" class="secondary">
                Latihan & kuis →
            </Link>
        </section>

        <!-- Bacaan terakhir (jika ada) -->
        <template v-if="recentReadings.length">
            <div class="section-head">
                <h2>Bacaan terakhir</h2>
                <Link :href="route('siswa.reading-history.index')" class="text-link">
                    Lihat riwayat →
                </Link>
            </div>
            <div class="content-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <LibraryMaterialCard
                    v-for="material in recentReadings.slice(0, 3)"
                    :key="material.id"
                    :material="material"
                    :saved="material.saved"
                />
            </div>
        </template>

        <!-- Pilihan dari koleksi (jika ada) -->
        <template v-if="featuredMaterials.length">
            <div class="section-head">
                <h2>Pilihan dari koleksi</h2>
                <Link :href="route('siswa.catalog.index')" class="text-link">
                    Lihat semua buku →
                </Link>
            </div>
            <div class="content-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <LibraryMaterialCard
                    v-for="material in featuredMaterials.slice(0, 3)"
                    :key="material.id"
                    :material="material"
                    :saved="material.saved"
                />
            </div>
        </template>
    </AuthenticatedLayout>
</template>
