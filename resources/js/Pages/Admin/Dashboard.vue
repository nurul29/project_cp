<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({ school_name: '', school_year: '', semester: '' }) },
    stats: { type: Object, default: () => ({ activeUsers: 0, totalUsers: 0, activeClasses: 0, subjects: 0, pendingRequests: 0 }) },
    requests: { type: Array, default: () => [] },
    recentActivities: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Beranda Admin — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Pengelolaan sekolah</div>
                <h1>Beranda administrator</h1>
                <p>Kelola akun, kelas, mata pelajaran, dan keamanan di SUNDIGITAL.</p>
            </div>
            <div class="page-actions">
                <Link :href="route('admin.users.index')" class="admin-btn solid">
                    + Kelola pengguna
                </Link>
            </div>
        </div>

        <div class="admin-dashboard">
            <!-- Welcome Hero Banner according to Mockup -->
            <section class="admin-welcome">
                <div>
                    <span>PENGELOLAAN SUNDIGITAL</span>
                    <h2>Perpustakaan yang tertata,<br />bacaan mudah ditemukan.</h2>
                    <p>{{ settings.school_name }} • {{ settings.school_year }} • Semester {{ settings.semester }}</p>
                </div>
                <div class="admin-period">
                    <small>PERLU PERHATIAN</small>
                    <strong>{{ stats.pendingRequests }}</strong>
                    <span>permintaan bantuan akun</span>
                    <Link :href="route('admin.account-requests.index')">
                        Tinjau permintaan →
                    </Link>
                </div>
            </section>

            <!-- 4 Metric Cards -->
            <div class="admin-stats">
                <article class="admin-stat">
                    <span>Pengguna aktif</span>
                    <strong>{{ stats.activeUsers }}</strong>
                    <small>Dari {{ stats.totalUsers }} akun terdaftar</small>
                </article>

                <article class="admin-stat">
                    <span>Kelas aktif</span>
                    <strong>{{ stats.activeClasses }}</strong>
                    <small>Kelompok belajar sekolah</small>
                </article>

                <article class="admin-stat">
                    <span>Mata pelajaran</span>
                    <strong>{{ stats.subjects }}</strong>
                    <small>Koleksi lintas pelajaran</small>
                </article>

                <article class="admin-stat">
                    <span>Bantuan tertunda</span>
                    <strong>{{ stats.pendingRequests }}</strong>
                    <small>Perlu verifikasi admin</small>
                </article>
            </div>

            <!-- Two Columns Overview -->
            <div class="admin-overview">
                <!-- Left: Perlu ditindaklanjuti -->
                <section class="panel">
                    <div class="panel-head">
                        <h2>Perlu ditindaklanjuti</h2>
                        <Link :href="route('admin.account-requests.index')" class="text-link">Lihat semua →</Link>
                    </div>

                    <div v-if="requests.length">
                        <div
                            v-for="req in requests"
                            :key="req.id"
                            class="admin-work-item"
                        >
                            <span class="task-mark" aria-hidden="true">↺</span>
                            <div>
                                <strong>{{ req.name || 'Permohonan bantuan akun' }}</strong>
                                <small>{{ req.username }} • {{ new Date(req.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</small>
                            </div>
                            <Link :href="route('admin.account-requests.index')" class="admin-btn">
                                Tinjau
                            </Link>
                        </div>
                    </div>
                    <p v-else class="muted">Tidak ada permohonan yang tertunda saat ini.</p>
                </section>

                <!-- Right: Akses cepat -->
                <section class="panel">
                    <div class="panel-head">
                        <h2>Akses cepat pengelolaan</h2>
                    </div>

                    <div class="admin-shortcuts">
                        <Link :href="route('admin.users.index')">
                            <span>👥</span>
                            <b>Kelola akun pengguna</b>
                            <small>Siswa, guru, dan staf sekolah</small>
                        </Link>
                        <Link :href="route('admin.classes.index')">
                            <span>🏫</span>
                            <b>Kelola kelas &amp; wali kelas</b>
                            <small>Struktur rombongan belajar</small>
                        </Link>
                        <Link :href="route('admin.subjects.index')">
                            <span>📚</span>
                            <b>Mata pelajaran</b>
                            <small>Kategori bacaan &amp; pengampu</small>
                        </Link>
                        <Link :href="route('admin.settings.index')">
                            <span>⚙</span>
                            <b>Pengaturan umum</b>
                            <small>Tahun ajaran dan semester aktif</small>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
