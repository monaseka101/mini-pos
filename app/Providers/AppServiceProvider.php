<?php

namespace App\Providers;

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

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
