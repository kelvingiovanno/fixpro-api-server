<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\QrCodeService;
use App\Services\WebAuthTokenService;
use App\Services\ReferralCodeService;
use App\Services\NonceCodeService;
use App\Services\StorageService;
use App\Services\AreaConfigService;
use App\Services\GoogleCalendarService;


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

        $this->app->singleton(StorageService::class, function () {
            return new StorageService();
        });

        $this->app->singleton(AreaConfigService::class , function () {
            return new AreaConfigService();
        });

        $this->app->singleton(NonceCodeService::class, function () {
            return new NonceCodeService();
        });

        $this->app->singleton(GoogleCalendarService::class, function () {
            return new GoogleCalendarService();
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
                cache()->forget('web_auth_token');
                
                // Generate and store a new token
                $authToken = WebAuthTokenService::generateAndStoreKey();
                echo "\n[APP AUTH TOKEN]: $authToken\n";
            }
        }
    }
}
