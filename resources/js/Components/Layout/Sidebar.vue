<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const page = usePage();
const role = computed(() => page.props.auth?.user?.role || 'student');
const isStudent = computed(() => role.value === 'student');
const isTeacher = computed(() => role.value === 'teacher');
const isAdmin = computed(() => role.value === 'admin');

const studentLinks = [
    { label: 'Beranda', icon: '⌂', route: 'siswa.dashboard', match: 'siswa.dashboard' },
    { label: 'Kategori pelajaran', icon: '▦', route: 'siswa.subjects.index', match: 'siswa.subjects.*' },
    { label: 'Katalog buku', icon: '▤', route: 'siswa.catalog.index', match: 'siswa.library.*|siswa.catalog.*' },
    { label: 'Video pendukung', icon: '▷', route: 'siswa.videos.index', match: 'siswa.videos.*' },
    { label: 'Latihan & kuis', icon: '✳', route: 'siswa.quizzes.index', match: 'siswa.quizzes.*' },
    { label: 'Nilai saya', icon: '▥', route: 'siswa.grades.index', match: 'siswa.grades.*' },
    { label: 'Rak saya', icon: '♡', route: 'siswa.shelf.index', match: 'siswa.shelf.*' },
    { label: 'Riwayat bacaan', icon: '◷', route: 'siswa.reading-history.index', match: 'siswa.reading-history.*' },
];

const teacherLinks = [
    { label: 'Beranda', icon: '⌂', href: '/guru' },
    { label: 'Koleksi Digital', icon: '📚', href: '/guru/materials' },
    { label: 'Kelola Kuis', icon: '🧩', href: '/guru/quizzes' },
    { label: 'Rekap Nilai Siswa', icon: '📊', href: '/guru/grade-report' },
];

const adminLinks = [
    { label: 'Ringkasan', icon: '⌂', href: '/admin' },
    { label: 'Pengguna', icon: '👥', href: '/admin/users' },
    { label: 'Permintaan akun', icon: '↺', href: '/admin/account-requests' },
    { label: 'Kelas', icon: '🏫', href: '/admin/classrooms' },
    { label: 'Mata pelajaran', icon: '📚', href: '/admin/subjects' },
    { label: 'Koleksi Digital', icon: '📂', href: '/admin/materials' },
    { label: 'Kelola Kuis', icon: '🧩', href: '/admin/quizzes' },
    { label: 'Log aktivitas', icon: '◷', href: '/admin/activity-logs' },
    { label: 'Pengaturan sekolah', icon: '⚙', href: '/admin/school-settings' },
];

const navLinks = computed(() => {
    if (isTeacher.value) return teacherLinks;
    if (isAdmin.value) return adminLinks;
    return studentLinks;
});

const isCurrent = (item) => {
    if (item.href) return false;
    try {
        return item.match.split('|').some((pattern) => route().current(pattern));
    } catch {
        return false;
    }
};

const initials = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'U';
});

const homeUrl = computed(() => {
    if (isTeacher.value) return '/guru';
    if (isAdmin.value) return '/admin';
    return route('siswa.dashboard');
});

const profileUrl = computed(() => {
    if (isTeacher.value) return '/guru/profile';
    if (isAdmin.value) return '/admin/profile';
    return route('profile.edit');
});

const userSubtitle = computed(() => {
    if (isStudent.value) {
        const cls = page.props.auth.user.classroom?.name || 'Kelas VIII-A';
        return `Siswa · ${cls}`;
    }
    if (isTeacher.value) {
        return 'Guru';
    }
    return 'Administrator';
});

const logout = () => router.post(route('logout'));
</script>

<template>
    <aside class="sidebar">
        <div class="brand-header-row">
            <Link :href="homeUrl" class="brand">
                <div class="brand-mark">
                    S<span class="brand-dot">•</span>
                </div>
                <div>
                    SUNDIGITAL
                    <small>SMP NEGERI 16 DEPOK</small>
                </div>
            </Link>
            <button
                type="button"
                class="sidebar-gemini-toggle"
                title="Tutup panel samping"
                aria-label="Tutup panel samping"
                @click="$emit('toggle-sidebar')"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="4"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <polyline points="15 9 12 12 15 15"></polyline>
                </svg>
            </button>
        </div>

        <div>
            <div class="side-label">Menu utama</div>
            <nav :aria-label="`Menu ${role}`" class="side-nav">
                <template v-for="item in navLinks" :key="item.label">
                    <a
                        v-if="item.href"
                        :href="item.href"
                    >
                        <span class="nav-icon" aria-hidden="true">{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </a>
                    <Link
                        v-else
                        :href="route(item.route)"
                        :aria-current="isCurrent(item) ? 'page' : undefined"
                        :class="{ active: isCurrent(item) }"
                    >
                        <span class="nav-icon" aria-hidden="true">{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </div>

        <div class="sidebar-bottom">
            <a
                v-if="profileUrl.startsWith('/admin') || profileUrl.startsWith('/guru')"
                :href="profileUrl"
                class="identity"
                :title="`Profil ${page.props.auth.user.name}`"
            >
                <div class="avatar" aria-hidden="true">{{ initials }}</div>
                <div>
                    <strong>{{ page.props.auth.user.name }}</strong>
                    <small>{{ userSubtitle }}</small>
                </div>
            </a>
            <Link
                v-else
                :href="profileUrl"
                class="identity"
                :title="`Profil ${page.props.auth.user.name}`"
            >
                <div class="avatar" aria-hidden="true">{{ initials }}</div>
                <div>
                    <strong>{{ page.props.auth.user.name }}</strong>
                    <small>{{ userSubtitle }}</small>
                </div>
            </Link>
            <button type="button" class="logout" @click="logout">
                Keluar akun →
            </button>
        </div>
    </aside>
</template>
