<?php

namespace App\Providers;

use App\Services\AreaService;
use App\Services\AuthService;
use App\Services\EncryptionService;
use App\Services\QrCodeService;
use App\Services\WebAuthTokenService;
use App\Services\ReferralCodeService;
use App\Services\NonceCodeService;
use App\Services\StorageService;    
use App\Services\GoogleCalendarService;
use App\Services\JoinFormService;
use App\Services\JoinAreaService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public $singletons = [
        EncryptionService::class => EncryptionService::class,
        QrCodeService::class => QrCodeService::class,
        StorageService::class => StorageService::class,
        ReferralCodeService::class => ReferralCodeService::class,
        NonceCodeService::class => NonceCodeService::class,
        GoogleCalendarService::class => GoogleCalendarService::class,
        AuthService::class => AuthService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JoinFormService::class, function () {
            return new JoinFormService(
                $this->app->make(AreaService::class),
            );
        });

        $this->app->singleton(JoinAreaService::class, function () {
            return new JoinAreaService(
                $this->app->make(NonceCodeService::class),
                $this->app->make(JoinFormService::class),
                $this->app->make(AreaService::class),
            );
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
