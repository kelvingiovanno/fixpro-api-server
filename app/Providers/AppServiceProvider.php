<?php

namespace App\Providers;

use App\Services\EncryptionService;
use App\Services\QrCodeService;
use App\Services\WebAuthTokenService;
use App\Services\EntryService;
use App\Services\StorageService;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        $this->app->singleton(EntryService::class, function () {
            return new EntryService();
        });

        $this->app->singleton(StorageService::class, function () {
            return new StorageService();
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
