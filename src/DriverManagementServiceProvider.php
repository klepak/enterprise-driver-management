<?php

namespace Klepak\DriverManagement;

use Illuminate\Support\ServiceProvider;
use Klepak\DriverManagement\Console\Commands\VendorCatalog\HP\HpCatalogProcessCommand;
use Klepak\DriverManagement\Console\Commands\VendorCatalog\HP\HpCatalogUpdateCommand;

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

        if ($this->app->runningInConsole()) {
            $this->commands([
                HpCatalogProcessCommand::class,
                HpCatalogUpdateCommand::class,
            ]);
        }
    }
}
