<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum; // 1. Thêm dòng này
use App\Models\PersonalAccessToken; // 2. Thêm dòng này

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
