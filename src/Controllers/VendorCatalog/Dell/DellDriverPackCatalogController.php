<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\Dell;

use Log;
use Klepak\DriverManagement\Models\Vendor\Dell\DellDriverPack;
use Klepak\ConsoleProgressBar\ConsoleProgressBar;

/**
 * @resource DellDriverPackCatalog
 *
 * Controller for Dell driver pack catalog
 */
class DellDriverPackCatalogController extends DellCatalogBaseController
{
    protected $catalogRelativeFtpPath = "catalog/DriverPackCatalog.cab";
    protected $localCatalogRelativePath = "DriverPackCatalog.xml";

    public function extractCatalog($catalogPath)
    {
        $parent = parent::extractCatalog($catalogPath);

        if($parent !== false)
        {
            $catalogFileContents = file_get_contents($parent);
            Log::info("REPLACE in $parent");
            $catalogFileContents = str_replace('xmlns="openmanage/cm/dm"', '', $catalogFileContents);
            file_put_contents($parent, $catalogFileContents);
        }
    }

    public function processDriverPackages()
    {
        $driverPackages = $this->getLocalCatalog()->xpath("//DriverPackage");

        $progress = (new ConsoleProgressBar)
            ->max(count($driverPackages))
            ->message('Processing driver packages');

        Log::info("Processing " . count($driverPackages) . " driver packages");

        $i = 0;
        foreach($driverPackages as $driverPackage)
        {
            $progress
                ->update(++$i);

            $attributes = ((array)$driverPackage->attributes())["@attributes"];
            $importantInfoAttributes = ((array)$driverPackage->ImportantInfo->attributes())["@attributes"];

            $osData = $this->parseSupportedOperatingSystems($driverPackage->SupportedOperatingSystems);

            DellDriverPack::updateOrCreate(
                [
                    "release_id" => $attributes["releaseID"],
                ],
                [
                    "hash_md5" => $attributes["hashMD5"],
                    "path" => $attributes["path"],
                    "date_time" => $attributes["dateTime"],
                    "vendor_version" => $attributes["vendorVersion"],
                    "dell_version" => $attributes["dellVersion"],
                    "type" => $attributes["type"],
                    "size" => $attributes["size"],

                    "name" => (string)$driverPackage->Name->Display,
                    "description" => (string)$driverPackage->Description->Display,

                    "supported_operating_systems" => $osData["os_codes"],
                    "supported_operating_system_languages" => $osData["os_languages"],
                    "supported_systems" => $this->parseSupportedSystems($driverPackage->SupportedSystems),

                    "info_url" => $importantInfoAttributes["URL"],
                ]
            );
        }

        $progress->completed();
    }

    public function processCatalog()
    {
        Log::info("Starting processing of {$this->getCatalogIdentifier()}");

        $this->getLocalCatalog();

        $this->processDriverPackages();
        $this->processComputerModels();
        #$this->processHardwareDevices();
        $this->processOperatingSystems();

        Log::info("Finished processing {$this->getCatalogIdentifier()}");
    }
}
