<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;

use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;
use Klepak\DriverManagement\Controllers\VendorCatalog\Lenovo\LenovoCatalogController;
use Klepak\DriverManagement\Models\Lenovo\LenovoDriverPackage;
use Klepak\DriverManagement\Models\Lenovo\LenovoComputerModel;

class LenovoDriverPackCatalogControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new LenovoCatalogController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(LenovoDriverPackage::count() > 0, 'No driver packages in DB');
        $this->assertTrue(LenovoComputerModel::count() > 0, 'No computer models in DB');
    }
}
