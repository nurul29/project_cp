<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const page = usePage();
const role = computed(() => page.props.auth?.user?.role || 'student');
const isStudent = computed(() => role.value === 'student');
const isTeacher = computed(() => role.value === 'teacher');
const isAdmin = computed(() => role.value === 'admin');

const homeUrl = computed(() => {
    if (isTeacher.value) return route('guru.dashboard');
    if (isAdmin.value) return route('admin.dashboard');
    return route('siswa.dashboard');
});

const titles = {
    // Siswa
    'siswa.dashboard': 'Perpustakaan digital',
    'siswa.subjects.index': 'Mata pelajaran',
    'siswa.library.index': 'Katalog buku',
    'siswa.catalog.index': 'Katalog buku',
    'siswa.shelf.index': 'Rak saya',
    'siswa.reading-history.index': 'Riwayat bacaan',
    'siswa.library.show': 'Buka materi',
    'siswa.videos.index': 'Video belajar',
    'siswa.worksheets.index': 'Lembar kerja',
    'siswa.worksheets.show': 'Detail lembar kerja',
    'siswa.quizzes.index': 'Kuis & latihan',
    'siswa.quizzes.show': 'Kerjakan kuis',
    'siswa.portfolio.index': 'Portofolio',
    'siswa.grades.index': 'Nilai saya',

    // Guru
    'guru.dashboard': 'Beranda guru',
    'guru.materials.index': 'Kelola materi',
    'guru.tasks.index': 'Tugas & kuis',
    'guru.submissions.index': 'Pengumpulan tugas',
    'guru.grades.index': 'Daftar nilai',

    // Admin
    'admin.dashboard': 'Ringkasan sekolah',
    'admin.users.index': 'Pengguna sekolah',
    'admin.classes.index': 'Kelas & wali kelas',
    'admin.subjects.index': 'Mata pelajaran',
    'admin.account-requests.index': 'Bantuan akun',
    'admin.activity-logs.index': 'Riwayat aktivitas',
    'admin.settings.index': 'Pengaturan sekolah',

    'profile.edit': 'Profil saya',
};

const title = computed(() => {
    try {
        const found = Object.entries(titles).find(([name]) => route().current(name));
        return found ? found[1] : 'Ruang belajar';
    } catch {
        return 'Ruang belajar';
    }
});

const initials = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'U';
});

const context = computed(() => {
    if (isStudent.value) {
        const classroom = page.props.auth.user.classroom?.name ?? 'Kelas VIII A';
        const count = page.props.subjectsCount ?? 11;
        return `${classroom} • ${count} pelajaran`;
    }

    if (isTeacher.value) {
        const teaching = page.props.auth.user.teachings?.[0];
        const subject = teaching?.subject?.name || 'Matematika';
        const classroom = teaching?.classroom?.name || 'Kelas VIII';
        return `${subject} • ${classroom}`;
    }

    return 'Pengelola sekolah';
});

const logout = () => router.post(route('logout'));
</script>

<template>
    <header class="app-topbar">
        <div class="topbar-left">
            <button
                type="button"
                class="sidebar-gemini-toggle topbar-gemini-toggle"
                title="Buka / tutup panel samping"
                aria-label="Buka atau tutup panel samping"
                @click="$emit('toggle-sidebar')"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="4"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <polyline points="13 9 16 12 13 15"></polyline>
                </svg>
            </button>
            <div class="breadcrumbs">
                <Link :href="homeUrl" class="hover:underline">SUNDIGITAL</Link> / <b>{{ title }}</b>
            </div>
        </div>
        <div class="topright">
            <div class="sem-chip">Semester Ganjil 2026/2027</div>
            <div class="class-chip">{{ context }}</div>
            <Link
                :href="route('profile.edit')"
                class="top-avatar"
                :title="`Profil ${page.props.auth.user.name}`"
                :aria-label="`Profil ${page.props.auth.user.name}`"
            >
                {{ initials }}
            </Link>
            <button
                type="button"
                class="mobile-exit"
                aria-label="Keluar dari akun"
                @click="logout"
            >
                Keluar
            </button>
        </div>
    </header>
</template>
