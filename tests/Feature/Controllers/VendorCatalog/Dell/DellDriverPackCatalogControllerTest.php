<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;


use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellDriverPackCatalogController;
use Klepak\DriverManagement\Models\Dell\DellComputerModel;
use Klepak\DriverManagement\Models\Dell\DellDriverPack;
use Klepak\DriverManagement\Models\Dell\DellOperatingSystem;

class DellDriverPackCatalogControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new DellDriverPackCatalogController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(DellComputerModel::count() > 0, 'No computer models in DB');
        $this->assertTrue(DellDriverPack::count() > 0, 'No driver packs in DB');
        $this->assertTrue(DellOperatingSystem::count() > 0, 'No operating systems in DB');
    }
}
