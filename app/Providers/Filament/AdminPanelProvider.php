<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Auth\EditProfile;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Auth\Register;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->databaseNotifications()
            ->default()
            ->profile(EditProfile::class, isSimple: false)
            // ->brandName('TL Gold Computer')
            ->brandLogo(FacadesStorage::url(path: 'default/bg.png'))
            ->brandLogoHeight('70px')
            ->id('admin')
            ->globalSearch(false)
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->sidebarWidth('16rem')
            ->navigationGroups([
                NavigationGroup::make('Inventory'),
                NavigationGroup::make('People')

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([

                //Pages\Dashboard::class,
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\SaleReport::class,
                //\App\Filament\Pages\ImportReport::class,
                \App\Filament\Pages\ProductReport::class,
                \App\Filament\Pages\UserReport::class,

            ])

            ->widgets([

                //  Widgets\AccountWidget::class,
                //  Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\SaleStats::class,
                //\App\Filament\Widgets\RevenueStats::class,
                \App\Filament\Widgets\InventoryStats::class,


                \App\Filament\Widgets\SalesChart::class,
                \App\Filament\Widgets\ProductImportChart::class,

                \App\Filament\Widgets\Brandpie::class,
                //\App\Filament\Widgets\Categorypie::class,

                \App\Filament\Widgets\LatestSale::class,
                \App\Filament\Widgets\SaleActivityChart::class,
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
