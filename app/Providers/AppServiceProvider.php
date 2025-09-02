<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Providers\Filament\CashierPanelProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
//     public function register()
//     {
// <<<<<<< HEAD
// =======
//         //$this->app->register(\App\Providers\Filament\CashierPanelProvider::class);
// >>>>>>> 8c30c670a9ec1afb31c671cb61f24a17e45bfe73
//     }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        // DB::listen(function ($query) {
        //     // Only log UPDATE statements
        //     if (str_starts_with(strtoupper(trim($query->sql)), 'UPDATE')) {
        //         Log::info('UPDATE Query: ' . $query->sql, [
        //             'bindings' => $query->bindings,
        //             'time' => $query->time . 'ms'
        //         ]);
        //     }
        // });
    }
}
