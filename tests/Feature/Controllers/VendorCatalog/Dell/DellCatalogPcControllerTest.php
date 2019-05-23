<?php

namespace Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\HP;


use Klepak\DriverManagement\Tests\Feature\Controllers\VendorCatalog\VendorCatalogBaseControllerTest;
use Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController;
use Klepak\DriverManagement\Models\Dell\DellSoftwareComponent;
use Klepak\DriverManagement\Models\Dell\DellComputerModel;
use Klepak\DriverManagement\Models\Dell\DellHardwareDevice;
use Klepak\DriverManagement\Models\Dell\DellOperatingSystem;

class DellCatalogPcControllerTest extends VendorCatalogBaseControllerTest
{
    /** @test */
    public function it_can_update_and_process_catalog()
    {
        $catalog = new DellCatalogPcController;

        $catalog->checkForCatalogUpdates();
        $catalog->processCatalog();

        $this->assertTrue(DellSoftwareComponent::count() > 0, 'No software components in DB');
        $this->assertTrue(DellComputerModel::count() > 0, 'No computer models in DB');
        $this->assertTrue(DellHardwareDevice::count() > 0, 'No hardware devices in DB');
        $this->assertTrue(DellOperatingSystem::count() > 0, 'No operating systems in DB');
    }
}
