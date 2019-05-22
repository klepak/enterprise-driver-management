<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;

use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;
use Klepak\DriverManagement\Models\HP\HpHardware;

class HpProductCatalogControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new HpProductCatalogController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(HpHardware::count() > 0, 'No hardware in DB');
    }
}
