<?php

namespace Klepak\DriverManagement\Console\Commands\VendorCatalog;

use Illuminate\Console\Command;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Lenovo\LenovoCatalogController;

class CatalogUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:update {vendors=hp,dell,lenovo : The vendors to update catalogs for.} {--dpc : Only update driver pack catalog.} {--pc : Only update product catalog.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for updates to Vendor Catalogs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $vendors = explode(',', $this->argument('vendors'));
        $pc = $this->option('pc');
        $dpc = $this->option('dpc');

        if(in_array('hp', $vendors))
        {
            if($dpc || (!$dpc && !$pc))
            {
                HpDriverPackCatalogController::checkForCatalogUpdates();
            }

            if($pc || (!$dpc && !$pc))
            {
                HpProductCatalogController::checkForCatalogUpdates();
            }
        }

        if(in_array('dell', $vendors))
        {
            if($dpc || (!$dpc && !$pc))
            {
                DellDriverPackCatalogController::checkForCatalogUpdates();
            }

            if($pc || (!$dpc && !$pc))
            {
                DellCatalogPcController::checkForCatalogUpdates();
            }
        }

        if(in_array('lenovo', $vendors))
        {
            LenovoCatalogController::checkForCatalogUpdates();
        }
    }
}
