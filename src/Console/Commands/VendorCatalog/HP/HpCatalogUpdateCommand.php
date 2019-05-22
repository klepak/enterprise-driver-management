<?php

namespace Klepak\DriverManagement\Console\Commands\VendorCatalog\HP;

use Illuminate\Console\Command;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

class HpCatalogUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hpcat:update {--dpc} {--pc}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for updates to HP Catalogs.';

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
        if($this->option('dpc') || (!$this->option('dpc') && !$this->option('pc')))
        {
            HpDriverPackCatalogController::checkForCatalogUpdates();
        }

        if($this->option('pc') || (!$this->option('dpc') && !$this->option('pc')))
        {
            HpProductCatalogController::checkForCatalogUpdates();
        }
    }
}
