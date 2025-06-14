<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\ResetPasswordViewResponse;
use App\Http\Responses\ResetPasswordViewResponse as CustomResetPasswordViewResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ResetPasswordViewResponse::class, CustomResetPasswordViewResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('it');
    }
}
