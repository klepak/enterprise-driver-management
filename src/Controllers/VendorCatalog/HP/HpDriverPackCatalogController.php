<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\HP;

use Log;
use Klepak\DriverManagement\Models\HP\HpDriverPack;

/**
 * @resource HpDriverPack
 *
 * Controller for HP driver packs
 */
class HpDriverPackCatalogController extends HpCatalogBaseController
{
    protected $catalogUpdateXmlUrl = "http://ftp.hp.com/pub/caps-softpaq/cmit/HPClientDriverPackCatalogUpdateInfo.xml";
    protected $localCatalogRelativePath = "HPClientDriverPackCatalog.xml";

    protected $updateCatalogBaseKey = "HPDriverPackCatalogUpdate";
    protected $catalogBaseKey = "HPClientDriverPackCatalog";
    protected $catalogVersionAttributeName = "DateReleased";

    public function processCatalog()
    {
        Log::info("Starting processing of driver pack catalog");

        $this->processProductOsDriverPack();

        Log::info("Finished processing driver pack catalog");
    }

    public function processProductOsDriverPack()
    {
        Log::info("Process driver packs from driver pack catalog");

        foreach($this->getLocalCatalog()->xpath("//ProductOSDriverPack") as $driverPack)
        {
            HpDriverPack::updateOrCreate(
                [
                    "product_type" => (string)$driverPack->ProductType,
                    "system_id" => (string)$driverPack->SystemId,
                    "system_name" => (string)$driverPack->SystemName,
                    "os_name" => (string)$driverPack->OSName,
                ],
                [
                    "softpaq_id" => (string)$driverPack->SoftPaqId,
                ]
            );
        }
    }

    public function extractCatalog($catalogPath)
    {
        return $this->extractCab($catalogPath);
    }
}
