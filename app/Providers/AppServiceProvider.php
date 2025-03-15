<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\QrCodeService;
use App\Services\ReferralCodeService;
use App\Services\ApiResponseService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EncryptionService::class, function () {
            return new EncryptionService();
        });

        $this->app->singleton(QrCodeService::class, function () {
            return new QrCodeService();
        });

        $this->app->singleton(ReferralCodeService::class, function () {
            return new ReferralCodeService();
        });

        $this->app->singleton(ApiResponseService::class, function () {
            return new ApiResponseService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
