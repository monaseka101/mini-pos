<?php

namespace App\Providers\Filament;

use App\Filament\Resources\SaleResource\Widgets\SaleChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\EnsureUserIsActive;
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
<<<<<<< HEAD
use Illuminate\Support\Facades\Storage;
=======
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage as FacadesStorage;
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->databaseNotifications()
            ->default()
            ->profile(EditProfile::class, isSimple: false)
<<<<<<< HEAD
            ->brandLogo(Storage::url(path: 'default/bg.png'))
            ->brandLogoHeight('60px')
            ->databaseNotifications()
            // ->brandName('Computer Shop')
            ->id('admin')
            ->globalSearch(false)
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->registration(Register::class)
            ->emailVerification()
=======
            // ->brandName('TL Gold Computer')
            ->brandLogo(FacadesStorage::url(path: 'default/bg.png'))
            ->brandLogoHeight('70px')
            ->id('admin')
            ->globalSearch(false)
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ->colors([
                'primary' => Color::Blue,
            ])
            // ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->navigationGroups([
                NavigationGroup::make('Inventory'),
<<<<<<< HEAD
                NavigationGroup::make('Customer & Supplier'),
                NavigationGroup::make('User Management'),
                NavigationGroup::make('Reports')
=======
                NavigationGroup::make('People')

>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
<<<<<<< HEAD
                Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // SaleChart::class
            // ])
=======

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
>>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
            ->middleware([
                // EnsureUserIsActive::class,
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
