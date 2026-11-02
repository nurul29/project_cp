<?php

namespace App\Providers\Filament;

use App\Filament\Teacher\Pages\Auth\TeacherEditProfile;
use App\Filament\Teacher\Pages\Dashboard;
use App\Filament\Teacher\Widgets\TeacherOverview;
use App\Filament\Teacher\Widgets\TeacherStatsOverview;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class GuruPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('guru')
            ->path('guru')
            ->brandLogo(fn () => view('filament.shared.brand'))
            ->brandLogoHeight('auto')
            ->font('Inter')
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->topbar(false)
            ->sidebarWidth('276px')
            ->maxContentWidth('full')
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn () => view('filament.shared.topbar'),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.shared.user-menu'),
            )
            ->viteTheme('resources/css/filament/guru/theme.css')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->profile(TeacherEditProfile::class, isSimple: false)
            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
            ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                TeacherOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
