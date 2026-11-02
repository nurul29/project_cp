<x-filament-widgets::widget>
    <div class="admin-dashboard">
        @if ($pendingCount > 0)
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 shadow-sm text-amber-900">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-lg">⚠️</span>
                    <div>
                        <strong class="text-sm font-bold">Permintaan Bantuan Lupa Kata Sandi Menunggu Tindakan</strong>
                        <p class="text-xs text-amber-800">Terdapat {{ $pendingCount }} permohonan reset kata sandi dari siswa/guru. Klik tombol di samping untuk menerapkan kata sandi standar sekolah.</p>
                    </div>
                </div>
                <a href="{{ $requestsUrl }}" class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-700 transition">
                    Tinjau Permintaan ({{ $pendingCount }}) →
                </a>
            </div>
        @endif

        <!-- Welcome Hero Banner -->
        <section class="admin-welcome">
            <div>
                <span>PENGELOLAAN SUNDIGITAL</span>
                <h2>Perpustakaan yang tertata,<br />bacaan mudah ditemukan.</h2>
                <p>{{ $settings->school_name }} • {{ $settings->school_year }} • Semester {{ $settings->semester }}</p>
            </div>
            <div class="admin-period">
                <small>PERLU PERHATIAN</small>
                <strong>{{ $pendingCount }}</strong>
                <span>permintaan bantuan akun</span>
                <a href="{{ $requestsUrl }}">Tinjau permintaan →</a>
            </div>
        </section>

        <!-- Stats 4 Cards Grid according to Mockup -->
        <div class="admin-stats">
            <article class="admin-stat">
                <span>Pengguna aktif</span>
                <strong>{{ $activeUsersCount }}</strong>
                <small>Dari {{ $totalUsersCount }} akun terdaftar</small>
            </article>

            <article class="admin-stat">
                <span>Kelas aktif</span>
                <strong>{{ $activeClassesCount }}</strong>
                <small>Kelompok belajar sekolah</small>
            </article>

            <article class="admin-stat">
                <span>Mata pelajaran</span>
                <strong>{{ $subjectsCount }}</strong>
                <small>Koleksi lintas pelajaran</small>
            </article>

            <article class="admin-stat">
                <span>Bantuan tertunda</span>
                <strong>{{ $pendingCount }}</strong>
                <small>Perlu verifikasi admin</small>
            </article>
        </div>

        <!-- Two Columns Overview: Perlu Ditindaklanjuti & Akses Cepat -->
        <div class="admin-overview">
            <section class="panel">
                <div class="panel-head">
                    <h2>Perlu ditindaklanjuti</h2>
                    <a class="text-link" href="{{ $requestsUrl }}">Lihat semua →</a>
                </div>

                @if ($requests->isNotEmpty())
                    @foreach ($requests as $request)
                        <div class="admin-work-item">
                            <span class="task-mark" aria-hidden="true">↺</span>
                            <div>
                                <strong>{{ $request->name ?? 'Permohonan bantuan akun' }}</strong>
                                <small>{{ $request->username }} • {{ $request->created_at->format('d M Y, H.i') }}</small>
                            </div>
                            <a class="admin-btn" href="{{ $requestsUrl }}">Tinjau</a>
                        </div>
                    @endforeach
                @else
                    <p class="muted">Semua permintaan sudah ditangani.</p>
                @endif
            </section>

            <section class="panel">
                <div class="panel-head">
                    <h2>Akses cepat</h2>
                </div>
                <div class="admin-shortcuts">
                    <a href="{{ $usersUrl }}">
                        <span aria-hidden="true">♙</span>
                        <b>Pengguna</b>
                        <small>Atur akun & akses</small>
                    </a>
                    <a href="{{ $classroomsUrl }}">
                        <span aria-hidden="true">▦</span>
                        <b>Kelas</b>
                        <small>Kelola kelompok belajar</small>
                    </a>
                    <a href="{{ $subjectsUrl }}">
                        <span aria-hidden="true">▤</span>
                        <b>Mata pelajaran</b>
                        <small>Atur guru pengampu</small>
                    </a>
                </div>
            </section>
        </div>

        <!-- Recent Activity Section -->
        <section class="panel admin-recent">
            <div class="panel-head">
                <h2>Aktivitas terbaru</h2>
                <a class="text-link" href="{{ $activityUrl }}">Lihat riwayat →</a>
            </div>

            @if ($activityLogs->isNotEmpty())
                @foreach ($activityLogs as $activity)
                    <div class="admin-recent-row">
                        <span>{{ $activity->description }}</span>
                        <small>{{ $activity->created_at->format('d M Y, H.i') }}</small>
                    </div>
                @endforeach
            @else
                <p class="muted">Belum ada aktivitas tercatat.</p>
            @endif
        </section>

        <p class="admin-demo-note">Pengelolaan terpusat SUNDIGITAL • SMP Negeri 16 Depok</p>
    </div>
</x-filament-widgets::widget>
