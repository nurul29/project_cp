<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\AdminEditProfile;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\AdminOverview;
use App\Filament\Admin\Widgets\AdminStatsOverview;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors(['primary' => Color::Teal])
            ->profile(AdminEditProfile::class, isSimple: false)
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([Dashboard::class])
            ->widgets([
                AdminOverview::class,
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
            ->authMiddleware([Authenticate::class]);
    }
}
