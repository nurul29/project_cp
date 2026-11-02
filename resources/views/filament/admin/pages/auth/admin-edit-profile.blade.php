@php
    $adminData = $this->getAdminData();
    $user = $adminData['user'];
    $initials = $adminData['initials'];
    $schoolSetting = $adminData['schoolSetting'];
    $stats = $adminData['stats'];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Banner Profil Administrator --}}
        <div class="relative overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 via-white to-slate-50 p-6 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-teal-700 text-2xl font-black text-white shadow-md shadow-teal-700/20">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                {{ $user->name }}
                            </h2>
                            <span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800">
                                Administrator Sistem
                            </span>
                            @if ($user->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                    ● Akun Aktif
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                            <span><strong>Username/ID:</strong> {{ $user->username }}</span>
                            <span class="text-gray-300">•</span>
                            <span><strong>Email:</strong> {{ $user->email ?? 'Belum terdaftar' }}</span>
                            <span class="text-gray-300">•</span>
                            <span><strong>Instansi:</strong> {{ $schoolSetting->school_name }}</span>
                        </p>
                    </div>
                </div>

                {{-- Status Tahun Ajaran --}}
                <div class="shrink-0 rounded-xl border border-teal-200/80 bg-white/90 p-3.5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-700">Tahun Ajaran Aktif</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">
                        {{ $schoolSetting->school_year }} • Semester {{ $schoolSetting->semester }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Grid Konten --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Kolom Kiri: Ringkasan Sistem & Statistik Sekolah (7 Kolom) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Statistik Sistem Sekolah --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-base font-bold text-gray-900">Ringkasan Ekosistem Sekolah</h3>
                        <p class="text-xs text-gray-500">Statistik data pengguna dan aktivitas pembelajaran di platform</p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 text-center">
                            <p class="text-xs font-semibold text-gray-500">Total Guru</p>
                            <p class="mt-1 text-2xl font-black text-teal-600">{{ $stats['total_teachers'] }}</p>
                            <p class="text-[10px] text-gray-400">Tenaga pengajar</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 text-center">
                            <p class="text-xs font-semibold text-gray-500">Total Siswa</p>
                            <p class="mt-1 text-2xl font-black text-indigo-600">{{ $stats['total_students'] }}</p>
                            <p class="text-[10px] text-gray-400">Peserta didik</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 text-center">
                            <p class="text-xs font-semibold text-gray-500">Rombel / Kelas</p>
                            <p class="mt-1 text-2xl font-black text-emerald-600">{{ $stats['total_classes'] }}</p>
                            <p class="text-[10px] text-gray-400">Kelas aktif</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 text-center">
                            <p class="text-xs font-semibold text-gray-500">Mata Pelajaran</p>
                            <p class="mt-1 text-2xl font-black text-amber-600">{{ $stats['total_subjects'] }}</p>
                            <p class="text-[10px] text-gray-400">Kurikulum aktif</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3 border-t border-gray-100 pt-4">
                        <div class="rounded-lg bg-teal-50/50 p-3">
                            <span class="text-xs font-medium text-teal-700">Materi Pembelajaran</span>
                            <p class="mt-0.5 text-lg font-bold text-teal-900">{{ $stats['total_materials'] }} item</p>
                        </div>
                        <div class="rounded-lg bg-indigo-50/50 p-3">
                            <span class="text-xs font-medium text-indigo-700">Kuis & Ujian</span>
                            <p class="mt-0.5 text-lg font-bold text-indigo-900">{{ $stats['total_quizzes'] }} paket</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50/50 p-3">
                            <span class="text-xs font-medium text-emerald-700">Penugasan Siswa</span>
                            <p class="mt-0.5 text-lg font-bold text-emerald-900">{{ $stats['total_assignments'] }} tugas</p>
                        </div>
                    </div>
                </div>

                {{-- Informasi Hak Akses & Kewenangan Admin --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900">Hak Akses & Otoritas Akun</h3>
                    <p class="text-xs text-gray-500">Akun ini memiliki hak akses administrator penuh terhadap seluruh modul sistem</p>

                    <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3">
                            <span class="text-xl">👥</span>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Manajemen Pengguna</h4>
                                <p class="text-[11px] text-gray-500">Kelola akun guru, siswa, dan admin</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3">
                            <span class="text-xl">🏫</span>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Manajemen Kelas & Rombel</h4>
                                <p class="text-[11px] text-gray-500">Atur tingkat, wali kelas, & penugasan</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3">
                            <span class="text-xl">📚</span>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Kurikulum & Pelajaran</h4>
                                <p class="text-[11px] text-gray-500">Mata pelajaran, materi, & kuis</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3">
                            <span class="text-xl">⚙️</span>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">Konfigurasi Sekolah</h4>
                                <p class="text-[11px] text-gray-500">Tahun ajaran, semester, & nama instansi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Formulir Pengaturan Akun Admin (5 Kolom) --}}
            <div class="space-y-6 lg:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-base font-bold text-gray-900">Pengaturan Akun & Keamanan</h3>
                        <p class="text-xs text-gray-500">Perbarui nama administrator atau kata sandi login</p>
                    </div>

                    <div class="mt-5">
                        <x-filament-panels::form id="form" wire:submit="save">
                            {{ $this->form }}

                            <div class="mt-6 border-t border-gray-100 pt-5">
                                <x-filament-panels::form.actions
                                    :actions="$this->getCachedFormActions()"
                                    :full-width="true"
                                />
                            </div>
                        </x-filament-panels::form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
