<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantService::class);
        $this->app->singleton(\App\Services\TenantDatabaseManager::class);
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        // Enable MongoDB Atlas or standalone query logging in debug mode
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
    }
}
