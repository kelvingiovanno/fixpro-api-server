<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\QrCodeService;
use App\Services\ReferralCodeService;
use App\Services\ApiResponseService;
use App\Services\AuthTokenService;

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
        if (app()->runningInConsole() && php_sapi_name() === 'cli') {
            if (in_array($_SERVER['argv'][1] ?? '', ['serve'])) {
                // Remove old token to force renewal
                cache()->forget('app_auth_token');
                
                // Generate and store a new token
                $authToken = AuthTokenService::generateAndStoreKey();
                echo "\n[APP AUTH TOKEN]: $authToken\n";

                // Store a flag in cache
                cache()->forever('app_auth_token_initialized', true);
            }
        }
    }
}
