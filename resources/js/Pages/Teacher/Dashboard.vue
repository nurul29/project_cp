<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    subjectName: { type: String, default: 'Matematika' },
    classesSummary: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({ school_name: '', school_year: '', semester: '' }) },
    stats: { type: Object, default: () => ({ readings: 0, published: 0, drafts: 0, support: 0, activeTasks: 0, pendingSubmissions: 0 }) },
    recentMaterials: { type: Array, default: () => [] },
    pendingSubmissions: { type: Array, default: () => [] },
    activeTasks: { type: Array, default: () => [] },
});

const page = usePage();
const teacherName = computed(() => page.props.auth.user?.name || 'Bapak/Ibu Guru');
</script>

<template>
    <Head title="Beranda Guru — SUNDIGITAL" />
    <AuthenticatedLayout>
        <div class="page-head">
            <div>
                <div class="eyebrow">Ruang guru</div>
                <h1>Selamat datang, {{ teacherName }}! 👋</h1>
                <p>Kelola pembelajaran dan koleksi perpustakaan untuk kelas yang diampu.</p>
            </div>
            <div class="page-actions">
                <Link :href="route('guru.materials.index')" class="admin-btn solid">
                    + Tambah materi
                </Link>
            </div>
        </div>

        <div class="teacher-dashboard">
            <!-- Context Pills -->
            <div class="teacher-context">
                <span class="soft-pill">{{ subjectName }}</span>
                <span>
                    {{ classesSummary.length ? classesSummary.map(c => c.name).join(' • ') : 'Belum ada kelas ditugaskan' }}
                </span>
                <span>Semester {{ settings.semester }} {{ settings.school_year }} • Koleksi perpustakaan sekolah</span>
            </div>

            <!-- Hero Section according to Mockup -->
            <section class="student-hero library-teacher-hero">
                <div class="copy">
                    <div class="mini">BERBAGI BACAAN, MEMBUKA WAWASAN</div>
                    <h2>Bangun koleksi yang<br />ingin dibaca siswa.</h2>
                    <p>Tambahkan buku, materi, dan modul PDF. Lengkapi bacaan dengan video belajar yang diputar di aplikasi.</p>
                    <Link :href="route('guru.materials.index')" class="lightbtn">
                        Kelola koleksi saya →
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

            <!-- Teacher 4 Stat Cards -->
            <div class="teacher-stats">
                <Link :href="route('guru.materials.index')" class="teacher-stat">
                    <div class="teacher-stat-top">
                        <span>Buku &amp; bacaan</span>
                        <b aria-hidden="true">▤</b>
                    </div>
                    <strong>{{ stats.readings }}</strong>
                    <div class="teacher-stat-bottom">
                        <span>PDF, modul, &amp; buku digital</span>
                        <span>Koleksi</span>
                    </div>
                </Link>

                <Link :href="route('guru.materials.index')" class="teacher-stat">
                    <div class="teacher-stat-top">
                        <span>Koleksi terbit</span>
                        <b aria-hidden="true">✓</b>
                    </div>
                    <strong>{{ stats.published }}</strong>
                    <div class="teacher-stat-bottom">
                        <span>Tersedia untuk siswa</span>
                        <span>Aktif</span>
                    </div>
                </Link>

                <Link :href="route('guru.tasks.index')" class="teacher-stat">
                    <div class="teacher-stat-top">
                        <span>Tugas aktif</span>
                        <b aria-hidden="true">📝</b>
                    </div>
                    <strong>{{ stats.activeTasks }}</strong>
                    <div class="teacher-stat-bottom">
                        <span>Aktivitas kelas</span>
                        <span>Tugas</span>
                    </div>
                </Link>

                <Link :href="route('guru.submissions.index')" class="teacher-stat attention">
                    <div class="teacher-stat-top">
                        <span>Perlu diperiksa</span>
                        <b aria-hidden="true">⏳</b>
                    </div>
                    <strong>{{ stats.pendingSubmissions }}</strong>
                    <div class="teacher-stat-bottom">
                        <span>Pengumpulan siswa</span>
                        <span>Menunggu</span>
                    </div>
                </Link>
            </div>

            <!-- Two Columns Layout -->
            <div class="teacher-grid">
                <!-- Left Column -->
                <div class="teacher-column">
                    <section class="panel teacher-panel">
                        <div class="panel-head">
                            <div>
                                <h2>Koleksi bacaan terbaru</h2>
                                <p>Buku dan materi yang baru saja ditambahkan.</p>
                            </div>
                            <Link :href="route('guru.materials.index')" class="text-link">Lihat semua →</Link>
                        </div>

                        <div v-if="recentMaterials.length">
                            <div
                                v-for="material in recentMaterials"
                                :key="material.id"
                                class="teacher-material-row"
                            >
                                <span class="teacher-doc" aria-hidden="true">
                                    {{ material.type === 'video' ? '▷' : '▤' }}
                                </span>
                                <div>
                                    <strong>{{ material.title }}</strong>
                                    <small>{{ material.subject?.name || subjectName }} • {{ material.classroom?.name || 'Semua kelas' }}</small>
                                </div>
                                <span class="admin-badge" :class="material.is_published ? 'good' : 'neutral'">
                                    {{ material.is_published ? 'Terbit' : 'Draf' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="teacher-quiet">
                            <span aria-hidden="true">📖</span>
                            <h3>Mulai koleksi pertamamu</h3>
                            <p>Tambahkan buku PDF atau bahan bacaan untuk siswa.</p>
                        </div>

                        <Link :href="route('guru.materials.index')" class="teacher-add-material">
                            + Tambahkan buku atau bacaan baru
                        </Link>
                    </section>

                    <!-- Pending submissions -->
                    <section class="panel teacher-panel">
                        <div class="panel-head">
                            <div>
                                <h2>Pengumpulan siswa terdekat</h2>
                                <p>Jawaban tugas yang memerlukan pemeriksaan.</p>
                            </div>
                            <Link :href="route('guru.submissions.index')" class="text-link">Buka pengumpulan →</Link>
                        </div>

                        <div v-if="pendingSubmissions.length">
                            <div
                                v-for="sub in pendingSubmissions"
                                :key="sub.id"
                                class="teacher-material-row"
                            >
                                <span class="avatar" style="width: 32px; height: 32px; font-size: 11px;">
                                    {{ sub.student?.name?.slice(0, 2)?.toUpperCase() || 'S' }}
                                </span>
                                <div>
                                    <strong>{{ sub.student?.name }} — {{ sub.assignment?.title }}</strong>
                                    <small>{{ sub.assignment?.subject?.name }} • Belum dinilai</small>
                                </div>
                                <Link :href="route('guru.submissions.index')" class="admin-btn">
                                    Periksa
                                </Link>
                            </div>
                        </div>
                        <p v-else class="muted" style="margin: 12px 0;">Semua tugas siswa telah diperiksa.</p>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="teacher-column">
                    <section class="panel teacher-panel upload-guide">
                        <h2>Peran guru di SUNDIGITAL</h2>
                        <p>Memilih bacaan, mengunggah modul belajar, dan merancang tugas terintegrasi agar siswa memiliki literasi yang kuat.</p>
                        <p><strong>Mata pelajaran</strong> menjadi kategori koleksi. <strong>Lembar kerja &amp; kuis</strong> menguji pemahaman dari buku yang dibaca.</p>
                    </section>

                    <section v-if="classesSummary.length" class="panel teacher-class-panel">
                        <span class="teacher-overline">KELAS YANG DIAMPU</span>
                        <div
                            v-for="cls in classesSummary"
                            :key="cls.id"
                            class="teacher-class-summary"
                        >
                            <div>
                                <h2>{{ cls.name }}</h2>
                                <p>{{ cls.subject }}</p>
                            </div>
                            <span><b>{{ cls.studentCount }}</b> siswa aktif</span>
                        </div>
                        <div class="teacher-class-footer">
                            <span>Rekap nilai kelas</span>
                            <Link :href="route('guru.grades.index')" class="text-link">Buka daftar nilai →</Link>
                        </div>
                    </section>

                    <section class="panel teacher-panel">
                        <div class="panel-head">
                            <h2>Aktivitas kelas aktif</h2>
                        </div>
                        <div v-if="activeTasks.length">
                            <div v-for="task in activeTasks" :key="task.id" class="lesson-line">
                                <span class="tile-icon peach">📝</span>
                                <div>
                                    <strong>{{ task.title }}</strong>
                                    <small>{{ task.classroom }} • Batas {{ task.due }}</small>
                                </div>
                            </div>
                        </div>
                        <p v-else class="muted">Belum ada tugas yang aktif saat ini.</p>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
