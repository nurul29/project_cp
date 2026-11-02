@php
    $user = auth()->user();
    $role = $user?->role?->value ?? ($user?->role ?? 'user');
    $initials = 'NA';
    if ($user?->name) {
        $parts = preg_split('/\s+/', trim($user->name));
        $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    if ($role === 'admin' || filament()->getId() === 'admin') {
        $roleLabel = 'Administrator';
    } elseif ($role === 'teacher' || filament()->getId() === 'guru') {
        $roleLabel = 'Guru';
    } else {
        $roleLabel = 'Siswa • ' . ($user?->classroom?->name ?? 'VIII A');
    }
@endphp

<div class="sidebar-bottom">
    <div class="user-menu" x-data="{ open: false }">
        <button
            type="button"
            class="identity user-trigger"
            id="user-trigger"
            aria-haspopup="menu"
            :aria-expanded="open"
            @click="open = !open"
        >
            <span class="avatar">{{ $initials }}</span>
            <span class="id-text">
                <strong>{{ $user->name }}</strong>
                <small>{{ $roleLabel }}</small>
            </span>
            <span class="chev" aria-hidden="true">⌃</span>
        </button>

        <div
            class="user-pop"
            id="user-pop"
            role="menu"
            x-show="open"
            @click.outside="open = false"
            style="display: none;"
        >
            <a href="{{ filament()->getProfileUrl() ?? route('profile.edit') }}" role="menuitem" class="user-pop-link">
                Profil saya
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" role="menuitem" class="danger w-full text-left">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>
