<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerification;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Livewire\MyProfileExtended;
use App\Settings\GeneralSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;
use Filament\View\PanelsRenderHook;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('user')
            ->path('/')
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification(EmailVerification::class)
            ->brandName(fn(GeneralSettings $settings) => $settings->brand_name)
            ->colors(fn(GeneralSettings $settings) => $settings->site_theme)
            ->databaseNotifications()->databaseNotificationsPolling('60s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin Panel')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->url('/admin')
                    ->sort(1)
                    ->visible(fn() => auth()->user()?->hasRole('super_admin')),
            ])
            ->spa()
            ->navigationGroups([
                NavigationGroup::make('Dokumen Aplikasi'),
                NavigationGroup::make('Asset'),
                NavigationGroup::make('Master Aset'),
            ])
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ManualBook::class,
                \App\Filament\Pages\TechnicalDocumentation::class,
                \App\Filament\Pages\MonitoringAsetScanner::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn() => view('footer')
            )
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
            ])
            ->plugins([
                \Jeffgreco13\FilamentBreezy\BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        navigationGroup: 'Settings',
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->myProfileComponents([
                        'personal_info' => MyProfileExtended::class,
                    ]),
            ]);
    }
}
