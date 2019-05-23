<?php

namespace Klepak\DriverManagement;

use Illuminate\Support\ServiceProvider;
use Klepak\DriverManagement\Console\Commands\VendorCatalog\CatalogProcessCommand;
use Klepak\DriverManagement\Console\Commands\VendorCatalog\CatalogUpdateCommand;

class DriverManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/drvmgmt.php' => config_path('drvmgmt.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CatalogProcessCommand::class,
                CatalogUpdateCommand::class,
            ]);
        }
    }
}
