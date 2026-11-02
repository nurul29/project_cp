@php
    $teacherData = $this->getTeacherData();
    $user = $teacherData['user'];
    $initials = $teacherData['initials'];
    $schoolSetting = $teacherData['schoolSetting'];
    $homeroomClassrooms = $teacherData['homeroomClassrooms'];
    $teachingGroups = $teacherData['teachingGroups'];
    $allClassrooms = $teacherData['allClassrooms'];
    $stats = $teacherData['stats'];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Banner Profil Guru --}}
        <div class="relative overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 via-white to-emerald-50/60 p-6 shadow-sm">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-teal-600 text-2xl font-black text-white shadow-md shadow-teal-600/20">
                        {{ $initials }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                                {{ $user->name }}
                            </h2>
                            <span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800">
                                Tenaga Pendidik / Guru
                            </span>
                            @if ($user->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                    ● Akun Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                            <span><strong>NIP/Username:</strong> {{ $user->username }}</span>
                            <span class="text-gray-300">•</span>
                            <span><strong>Email:</strong> {{ $user->email ?? 'Belum terdaftar' }}</span>
                            <span class="text-gray-300">•</span>
                            <span><strong>Instansi:</strong> {{ $schoolSetting->school_name }}</span>
                        </p>
                    </div>
                </div>

                {{-- Status Wali Kelas Highlight --}}
                <div class="shrink-0 rounded-xl border border-teal-200/80 bg-white/90 p-3.5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-teal-700">Tanggung Jawab Wali Kelas</p>
                    <div class="mt-1 flex items-center gap-2">
                        @if ($homeroomClassrooms->isNotEmpty())
                            <span class="text-lg">🎓</span>
                            <span class="text-sm font-bold text-gray-900">
                                Wali Kelas {{ $homeroomClassrooms->pluck('name')->implode(', ') }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500">
                                Bukan Wali Kelas
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid Konten: Info Akademik & Penugasan Mengajar vs Form Pengaturan Profil --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Kolom Kiri: Informasi Penugasan Mengajar (7 Kolom) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Statistik Kontribusi & Penugasan --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Kelas Diampu</p>
                        <p class="mt-1 text-2xl font-extrabold text-teal-600">{{ $stats['total_classes'] }}</p>
                        <p class="text-[11px] text-gray-400">Rombongan belajar</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Mata Pelajaran</p>
                        <p class="mt-1 text-2xl font-extrabold text-indigo-600">{{ $stats['total_subjects'] }}</p>
                        <p class="text-[11px] text-gray-400">Bidang studi</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Materi Unggahan</p>
                        <p class="mt-1 text-2xl font-extrabold text-emerald-600">{{ $stats['materials_count'] }}</p>
                        <p class="text-[11px] text-gray-400">Koleksi materi</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <p class="text-xs font-medium text-gray-500">Kuis & Tugas</p>
                        <p class="mt-1 text-2xl font-extrabold text-amber-600">{{ $stats['quizzes_count'] + $stats['assignments_count'] }}</p>
                        <p class="text-[11px] text-gray-400">Instrumen evaluasi</p>
                    </div>
                </div>

                {{-- Detail Kelas yang Diampu per Mata Pelajaran --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Rincian Penugasan Mengajar</h3>
                            <p class="text-xs text-gray-500">Daftar kelas dan mata pelajaran yang sedang diampu pada tahun ajaran ini</p>
                        </div>
                        <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                            T.A. {{ $schoolSetting->school_year }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-4">
                        @forelse ($teachingGroups as $group)
                            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 transition-all hover:bg-gray-50">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-base shadow-sm">
                                            {{ $group['subject']?->icon ?? '📖' }}
                                        </span>
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $group['subject']?->name ?? 'Mata Pelajaran' }}</h4>
                                            <p class="text-xs text-gray-500">Kode: <span class="font-semibold text-gray-700">{{ $group['subject']?->code ?? '-' }}</span></p>
                                        </div>
                                    </div>
                                    <span class="inline-flex w-fit items-center rounded-md bg-white px-2.5 py-1 text-xs font-medium text-gray-600 shadow-sm border border-gray-200">
                                        {{ $group['classrooms']->count() }} Kelas Diampu
                                    </span>
                                </div>

                                <div class="mt-3.5 border-t border-gray-200/60 pt-3">
                                    <p class="text-xs font-semibold text-gray-500">Daftar Rombel / Kelas:</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @forelse ($group['classrooms'] as $classroom)
                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-white px-3 py-1 text-xs font-bold text-teal-800 shadow-sm">
                                                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                                Kelas {{ $classroom->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs italic text-gray-400">Belum ada kelas yang terhubung</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center">
                                <span class="text-3xl">📋</span>
                                <h4 class="mt-2 text-sm font-bold text-gray-800">Belum Ada Penugasan Mengajar</h4>
                                <p class="mt-1 text-xs text-gray-500">
                                    Akun Anda belum memiliki daftar rombel mengajar. Hubungi admin kurikulum untuk menambahkan kelas penugasan.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 rounded-xl bg-amber-50/70 border border-amber-200/70 p-3.5">
                        <div class="flex items-start gap-2.5">
                            <span class="text-base text-amber-600">💡</span>
                            <div class="text-xs leading-relaxed text-amber-800">
                                <strong>Catatan Penugasan:</strong> Penugasan mata pelajaran, rombongan belajar, dan penunjukan wali kelas dikelola secara terpusat oleh Administrator. Hubungi pihak kurikulum sekolah jika terdapat perubahan jadwal mengajar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Form Ubah Profil & Password Filament (5 Kolom) --}}
            <div class="space-y-6 lg:col-span-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="text-base font-bold text-gray-900">Pengaturan Akun & Keamanan</h3>
                        <p class="text-xs text-gray-500">Perbarui nama tampilan atau kata sandi login Anda</p>
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
