<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
use App\Services\QuickChartService;
use App\Services\TicketService;

use App\Services\Reports\PeriodicReport;
use App\Services\Reports\PrintViewReport;
use App\Services\Reports\ServiceFormReport;
use App\Services\Reports\TicketReport;
use App\Services\Reports\WorkOrderReport;


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

        $this->app->singleton(TicketService::class, function () {
            return new TicketService(
                $this->app->make(ServiceFormReport::class),
                $this->app->make(WorkOrderReport::class),
                $this->app->make(StorageService::class),
                $this->app->make(AreaService::class),
            );
        });

        $this->app->singleton(ServiceFormReport::class, function () {
            return new ServiceFormReport(
                $this->app->make(AreaService::class),
            );
        });

        $this->app->singleton(WorkOrderReport::class, function () {
            return new WorkOrderReport(
                $this->app->make(AreaService::class),
            );
        });

        $this->app->singleton(PrintViewReport::class, function () {
            return new PrintViewReport(
                $this->app->make(AreaService::class),
            );
        });
        
        $this->app->singleton(TicketReport::class, function () {
            return new TicketReport(
                $this->app->make(AreaService::class),
            );
        });

        $this->app->singleton(PeriodicReport::class, function () {
            return new PeriodicReport(
                $this->app->make(AreaService::class),
                $this->app->make(TicketService::class),
                $this->app->make(QuickChartService::class),
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
                
                cache()->forget('web_auth_token');
                
                $authToken = WebAuthTokenService::generateAndStoreKey();
                echo "\n[APP AUTH TOKEN]: $authToken\n";
            }
        }
        
    }
}
