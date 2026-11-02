@php
    $user = auth()->user();
    $role = $user?->role?->value ?? ($user?->role ?? 'user');
    $isAdmin = $role === 'admin' || filament()->getId() === 'admin';
    $isTeacher = $role === 'teacher' || filament()->getId() === 'guru';

    $initials = 'NA';
    if ($user?->name) {
        $parts = preg_split('/\s+/', trim($user->name));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    $area = $isAdmin ? 'Pengelolaan Sekolah' : ($isTeacher ? 'Ruang Guru' : 'Ruang Siswa');
    $settings = \App\Models\SchoolSetting::current();
    $semesterText = 'Semester ' . ($settings->semester ?? 'Ganjil') . ' ' . ($settings->school_year ?? '2026/2027');

    if ($isAdmin) {
        $classChip = 'Pengelola sekolah';
    } elseif ($isTeacher) {
        $teachings = $user?->teachings()->with(['subject:id,name', 'classroom:id,name'])->get() ?? collect();
        $subjects = $teachings->pluck('subject.name')->filter()->unique()->values();
        $classes = $teachings->pluck('classroom.name')->filter()->unique()->values();

        $subjLabel = $subjects->isEmpty()
            ? 'Guru'
            : ($subjects->count() <= 2 ? $subjects->join(', ') : $subjects->first() . ' +' . ($subjects->count() - 1) . ' mapel');

        $clsLabel = $classes->isEmpty()
            ? 'Semua kelas'
            : ($classes->count() <= 2 ? $classes->join(', ') : $classes->count() . ' Kelas');

        $classChip = $subjLabel . ' • ' . $clsLabel;
    } else {
        $classChip = ($user?->classroom?->name ?? 'Kelas VIII A') . ' • 11 pelajaran';
    }

    $pageTitle = $isAdmin ? 'Ringkasan' : 'Beranda';
    if (isset($title) && $title) {
        $pageTitle = $title;
    } else {
        $path = request()->path();
        $pageTitle = match (true) {
            str_contains($path, 'admin/users') => 'Pengguna',
            str_contains($path, 'admin/classrooms') => 'Kelas',
            str_contains($path, 'admin/subjects') => 'Mata pelajaran',
            str_contains($path, 'admin/materials') => 'Koleksi Digital',
            str_contains($path, 'admin/quizzes') => 'Kelola Kuis',
            str_contains($path, 'admin/account-requests') => 'Permintaan akun',
            str_contains($path, 'admin/activity-logs') => 'Log aktivitas',
            str_contains($path, 'admin/school-settings') => 'Pengaturan sekolah',
            str_contains($path, 'guru/materials') => 'Koleksi Digital',
            str_contains($path, 'guru/quizzes') => 'Kelola Kuis',
            str_contains($path, 'guru/grade-report') => 'Rekap Nilai',
            default => $pageTitle,
        };
    }
@endphp

<div
    class="sd-sidebar-backdrop"
    onclick="window.closeSundigitalMobileSidebar && window.closeSundigitalMobileSidebar()"
    aria-hidden="true"
></div>

<header class="app-topbar">
    <div class="topbar-left">
        <button
            type="button"
            class="sidebar-gemini-toggle topbar-gemini-toggle"
            onclick="window.toggleSundigitalSidebar && window.toggleSundigitalSidebar(event)"
            title="Buka / tutup panel samping"
            aria-label="Buka atau tutup panel samping"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="4"></rect>
                <line x1="9" y1="3" x2="9" y2="21"></line>
                <polyline points="13 9 16 12 13 15"></polyline>
            </svg>
        </button>
        <span class="breadcrumbs">
            <span class="crumb-brand">SUNDIGITAL /</span> <span id="crumb-area">{{ $area }}</span> / <b id="crumb-page">{{ $pageTitle }}</b>
        </span>
    </div>
    <div class="topright">
        @if (! $isAdmin)
            <span class="sem-chip" id="sem-chip">
                <i aria-hidden="true"></i>
                <span id="sem-text">{{ $semesterText }}</span>
            </span>
        @endif
        <span class="class-chip" id="top-chip">{{ $classChip }}</span>
        <a href="{{ filament()->getProfileUrl() ?? route('profile.edit') }}" class="top-avatar" aria-label="Buka profil saya" title="Profil saya">
            {{ $initials }}
        </a>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="mobile-exit" aria-label="Keluar dari akun">Keluar</button>
        </form>
    </div>
</header>

<script>
    (function () {
        const STORAGE_KEY = 'sundigital_sidebar_collapsed';

        function applySidebarState() {
            const isMobile = window.innerWidth < 1024;
            if (!isMobile) {
                document.body.classList.remove('sd-sidebar-mobile-open');
                const collapsed = localStorage.getItem(STORAGE_KEY) === '1';
                document.body.classList.toggle('sd-sidebar-collapsed', collapsed);
            } else {
                document.body.classList.remove('sd-sidebar-collapsed');
            }
        }

        window.toggleSundigitalSidebar = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const isMobile = window.innerWidth < 1024;
            if (isMobile) {
                document.body.classList.toggle('sd-sidebar-mobile-open');
            } else {
                const next = !document.body.classList.contains('sd-sidebar-collapsed');
                document.body.classList.toggle('sd-sidebar-collapsed', next);
                localStorage.setItem(STORAGE_KEY, next ? '1' : '0');
            }
        };

        window.closeSundigitalMobileSidebar = function () {
            document.body.classList.remove('sd-sidebar-mobile-open');
        };

        applySidebarState();
        window.addEventListener('resize', applySidebarState);
        document.addEventListener('livewire:navigated', applySidebarState);
    })();
</script>
