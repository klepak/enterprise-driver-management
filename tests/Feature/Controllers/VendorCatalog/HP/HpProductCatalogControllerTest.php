<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;

use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;
use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;
use Klepak\DriverManagement\Models\Vendor\HP\HpHardware;
use Klepak\DriverManagement\Models\Vendor\HP\HpSoftpaq;
use Klepak\DriverManagement\Models\Vendor\HP\HpLanguage;
use Klepak\DriverManagement\Models\Vendor\HP\HpOperatingSystem;
use Klepak\DriverManagement\Models\Vendor\HP\HpComputerModel;

class HpProductCatalogControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new HpProductCatalogController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(HpHardware::count() > 0, 'No hardware in DB');
        $this->assertTrue(HpSoftpaq::count() > 0, 'No hardware in DB');
        $this->assertTrue(HpLanguage::count() > 0, 'No hardware in DB');
        $this->assertTrue(HpOperatingSystem::count() > 0, 'No hardware in DB');
        $this->assertTrue(HpComputerModel::count() > 0, 'No hardware in DB');
    }
}
