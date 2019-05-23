<?php

namespace Klepak\DriverManagement\Console\Commands\VendorCatalog;

use Illuminate\Console\Command;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController;

class CatalogProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:process {vendors=hp,dell,lenovo : The vendors to process catalogs for.} {--dpc : Only process driver pack catalog.} {--pc : Only process product catalog.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process already downloaded Vendor Catalogs.';

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
                $catalog = new HpDriverPackCatalogController;
                $catalog->processCatalog();
            }

            if($pc || (!$dpc && !$pc))
            {
                $catalog = new HpProductCatalogController;
                $catalog->processCatalog();
            }
        }

        if(in_array('dell', $vendors))
        {
            if($dpc || (!$dpc && !$pc))
            {
                $catalog = new DellDriverPackCatalogController;
                $catalog->processCatalog();
            }

            if($pc || (!$dpc && !$pc))
            {
                $catalog = new DellCatalogPcController;
                $catalog->processCatalog();
            }
        }
    }
}
