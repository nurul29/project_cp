<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
    profileContext: { type: Object, required: true },
});

const initials = props.profileContext.name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();
</script>

<template>
    <Head title="Profil saya" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl">
            <header class="mb-6">
                <p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-[#0c7774]">Pengaturan Akun</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#193b3a] sm:text-3xl">Profil Siswa</h1>
                <p class="mt-2 text-sm text-[#667b79]">Informasi identitas siswa, kelas rombongan belajar, dan keamanan akun.</p>
            </header>

            <div class="grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
                <!-- Sidebar Profil Siswa -->
                <aside class="space-y-5">
                    <div class="rounded-2xl border border-[#e4eae7] bg-white p-6 text-center shadow-sm">
                        <span class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-[#e9f7f1] to-[#d2f0e4] text-2xl font-black text-[#0c7774] shadow-sm" aria-hidden="true">
                            {{ initials }}
                        </span>
                        <h2 class="mt-4 text-lg font-extrabold text-[#193b3a]">{{ profileContext.name }}</h2>
                        <p class="mt-1 text-xs font-semibold text-[#667b79]">NISN / Username: <span class="font-bold text-[#193b3a]">{{ profileContext.username }}</span></p>

                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <span class="rounded-full bg-[#f1f6f3] px-3 py-1 text-xs font-bold text-[#526764]">Peserta Didik</span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold" :class="profileContext.isActive ? 'bg-[#e9f7f1] text-[#075f5d]' : 'bg-[#f1f3f2] text-[#667b79]'">
                                {{ profileContext.isActive ? '● Akun Aktif' : 'Akun Nonaktif' }}
                            </span>
                        </div>

                        <div class="mt-5 border-t border-[#edf1ee] pt-4 text-xs font-medium text-[#667b79]">
                            <p class="font-bold text-[#193b3a]">{{ profileContext.school }}</p>
                            <p class="mt-0.5 text-[11px] text-[#819694]">Tahun Ajaran {{ profileContext.schoolYear }}</p>
                        </div>
                    </div>

                    <!-- Highlight Wali Kelas -->
                    <div class="rounded-2xl border border-teal-100 bg-[#f4fbf8] p-5 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-xl">
                                👨‍🏫
                            </span>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Wali Kelas Anda</p>
                                <p class="text-sm font-extrabold text-[#193b3a]">{{ profileContext.homeroomTeacher }}</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="space-y-6">
                    <!-- Ringkasan Statistik Aktivitas Siswa -->
                    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl border border-[#e4eae7] bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-[#667b79]">Buku di Rak</p>
                            <p class="mt-1 text-2xl font-black text-[#0c7774]">{{ profileContext.stats?.shelfCount ?? 0 }}</p>
                            <p class="text-[10px] text-[#819694]">Koleksi favorit</p>
                        </div>
                        <div class="rounded-2xl border border-[#e4eae7] bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-[#667b79]">Buku Dibaca</p>
                            <p class="mt-1 text-2xl font-black text-indigo-600">{{ profileContext.stats?.readingCount ?? 0 }}</p>
                            <p class="text-[10px] text-[#819694]">Riwayat literasi</p>
                        </div>
                        <div class="rounded-2xl border border-[#e4eae7] bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-[#667b79]">Tugas Selesai</p>
                            <p class="mt-1 text-2xl font-black text-emerald-600">{{ profileContext.stats?.submissionsCount ?? 0 }}</p>
                            <p class="text-[10px] text-[#819694]">Terkumpul</p>
                        </div>
                        <div class="rounded-2xl border border-[#e4eae7] bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-[#667b79]">Kuis Dikerjakan</p>
                            <p class="mt-1 text-2xl font-black text-amber-600">{{ profileContext.stats?.quizAttemptsCount ?? 0 }}</p>
                            <p class="text-[10px] text-[#819694]">Evaluasi tuntas</p>
                        </div>
                    </section>

                    <!-- Informasi Akademik & Rombel Siswa -->
                    <section class="rounded-2xl border border-[#e4eae7] bg-white p-5 shadow-sm sm:p-6">
                        <div class="border-b border-[#edf1ee] pb-3">
                            <h2 class="text-base font-extrabold text-[#193b3a]">Informasi Akademik & Akun</h2>
                            <p class="text-xs text-[#667b79]">Data kelas dan identitas resmi terdaftar di sekolah</p>
                        </div>

                        <dl class="mt-4 divide-y divide-[#edf1ee]">
                            <div class="grid gap-1 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-xs font-semibold text-[#667b79]">Nama Lengkap</dt>
                                <dd class="text-sm font-bold text-[#193b3a]">{{ profileContext.name }}</dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-xs font-semibold text-[#667b79]">NISN / Username</dt>
                                <dd class="text-sm font-bold text-[#193b3a]">{{ profileContext.username }}</dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-xs font-semibold text-[#667b79]">Rombongan Belajar (Kelas)</dt>
                                <dd class="flex items-center gap-2 text-sm font-bold text-[#193b3a]">
                                    <span class="inline-flex items-center rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-black text-teal-800 border border-teal-200">
                                        {{ profileContext.classroom }}
                                    </span>
                                </dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-xs font-semibold text-[#667b79]">Wali Kelas</dt>
                                <dd class="text-sm font-bold text-[#193b3a]">{{ profileContext.homeroomTeacher }}</dd>
                            </div>
                            <div class="grid gap-1 py-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                                <dt class="text-xs font-semibold text-[#667b79]">Email Siswa</dt>
                                <dd class="break-all text-sm font-bold text-[#193b3a]">{{ profileContext.email || 'Belum ditambahkan' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 rounded-xl bg-[#f4f8f5] p-3.5 text-xs leading-5 text-[#667b79]">
                            💡 <strong>Catatan:</strong> Data nama, NISN, dan rombongan belajar (kelas) ditentukan oleh pihak sekolah. Jika terdapat kekeliruan data rombel atau kelas, silakan laporkan ke wali kelas atau bagian tata usaha sekolah.
                        </div>
                    </section>

                    <!-- Form Pengaturan Informasi -->
                    <section class="rounded-2xl border border-[#e4eae7] bg-white p-5 shadow-sm sm:p-6">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </section>

                    <!-- Form Kata Sandi -->
                    <section class="rounded-2xl border border-[#e4eae7] bg-white p-5 shadow-sm sm:p-6">
                        <UpdatePasswordForm />
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
