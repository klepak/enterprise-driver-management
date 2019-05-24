<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;

use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpDriverPackCatalogController;
use Klepak\DriverManagement\Models\Vendor\HP\HpDriverPack;
use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;

class HpDriverPackCatalogControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new HpDriverPackCatalogController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(HpDriverPack::count() > 0, 'No driver packs in DB');
    }
}
