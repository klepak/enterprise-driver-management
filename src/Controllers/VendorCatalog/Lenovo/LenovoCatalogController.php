<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\Lenovo;

use Klepak\DriverManagement\Controllers\VendorCatalog\VendorCatalogBaseController;
use Log;
use Klepak\DriverManagement\Models\Lenovo\LenovoDriverPackage;
use Klepak\DriverManagement\Models\Lenovo\LenovoComputerModel;

/**
 * @resource LenovoCatalog
 *
 * Controller for Lenovo catalog
 */
class LenovoCatalogController extends VendorCatalogBaseController
{
    protected static $storagePath = "hardware\\catalog\\lenovo";

    public $catalogSourceUrl = "http://download.lenovo.com/cdrt/td/catalog.xml";
    protected $localCatalogRelativePath = "catalog.xml";

    public $driverPacks = [];
    public $computerModels = [];

    public function downloadUpdateXml()
    {
        Log::info("Starting download of update xml");

        return $this->downloadFile($this->catalogUpdateXmlUrl);
    }

    public function isLocalCatalogOutdated()
    {
        return true;
    }

    public function extractCatalog($catalogPath)
    {
        return $catalogPath;
    }

    public function processComputerModels()
    {
        Log::info("Process " . count($this->computerModels) . " computer models");

        foreach($this->computerModels as $systemId => $data)
        {
            LenovoComputerModel::updateOrCreate(
                [
                    'model_id' => (string)$systemId, // sql query bugs out if not explicitly cast to string
                    'system_id' => (string)$systemId, // sql query bugs out if not explicitly cast to string
                ], $data
            );
        }
    }

    public function parseCatalog()
    {
        $products = $this->getLocalCatalog()->xpath("//Product");

        Log::info("Process " . count($products) . " product entries");

        foreach($products as $product)
        {
            $attributes = ((array)$product->attributes())["@attributes"];

            $modelName = (string)$product->Queries->Version;
            $systemId = $attributes["model"];
            $operatingSystem = $attributes["os"];

            $driverPackIds = [];
            foreach($product->DriverPack as $driverPack)
            {
                $driverPackAttributes = ((array)$driverPack->attributes())["@attributes"];
                $url = (string)$driverPack;
                $packageId = basename($url);

                if(!in_array($packageId, ["no winpe", "not available"]))
                {
                    if(!isset($this->driverPacks[$packageId]))
                    {
                        $driverPackIds[] = $packageId;

                        $this->driverPacks[$packageId] = [
                            "type" => "driver_pack",
                            "category" => $driverPackAttributes["id"],
                            "name" => "Driver Pack",
                            "download_url" => $url,
                            "date" => isset($driverPackAttributes["date"]) ? $driverPackAttributes["date"] : "",

                            "supported_models" => [$systemId],
                            "supported_operating_systems" => [$operatingSystem],
                            "_supported_model_names" => [$modelName],
                        ];
                    }
                    else
                    {
                        if(!in_array($systemId, $this->driverPacks[$packageId]["supported_models"]))
                            $this->driverPacks[$packageId]["supported_models"][] = $systemId;

                        if(!in_array($operatingSystem, $this->driverPacks[$packageId]["supported_operating_systems"]))
                            $this->driverPacks[$packageId]["supported_operating_systems"][] = $operatingSystem;

                        if(!in_array($modelName, $this->driverPacks[$packageId]["_supported_model_names"]))
                            $this->driverPacks[$packageId]["_supported_model_names"][] = $modelName;
                    }
                }
            }

            $swDriverPackIds = [];
            foreach($product->HardwareApps->HardwareApp as $hardwareApp)
            {
                $hardwareAppAttributes = ((array)$hardwareApp->attributes())["@attributes"];
                $url = (string)$hardwareApp->downloadURL;
                $packageId = basename($url);

                if(!isset($this->driverPacks[$packageId]))
                {
                    $swDriverPackIds[] = $packageId;

                    $this->driverPacks[$packageId] = [
                        "type" => "hardware_app",
                        "category" => $hardwareAppAttributes["category"],
                        "name" => (string)$hardwareApp->name,
                        "download_url" => $url,
                        "install_cmd" => (string)$hardwareApp->installCmd,
                        "supported_models" => [$systemId],
                        "supported_operating_systems" => [$operatingSystem],
                    ];
                }
                else
                {
                    if(!in_array($systemId, $this->driverPacks[$packageId]["supported_models"]))
                        $this->driverPacks[$packageId]["supported_models"][] = $systemId;

                    if(!in_array($operatingSystem, $this->driverPacks[$packageId]["supported_operating_systems"]))
                        $this->driverPacks[$packageId]["supported_operating_systems"][] = $operatingSystem;
                }
            }

            $biosPackageId = basename((string)$product->BIOSUpdate);
            if(!empty($biosPackageId))
            {
                if(!isset($this->driverPacks[$biosPackageId]))
                {
                    $this->driverPacks[$biosPackageId] = [
                        "type" => "bios_update",
                        "supported_models" => [$systemId],
                        "supported_operating_systems" => [$operatingSystem],
                    ];
                }
                else
                {
                    if(!in_array($systemId, $this->driverPacks[$biosPackageId]["supported_models"]))
                        $this->driverPacks[$biosPackageId]["supported_models"][] = $systemId;

                    if(!in_array($operatingSystem, $this->driverPacks[$biosPackageId]["supported_operating_systems"]))
                        $this->driverPacks[$biosPackageId]["supported_operating_systems"][] = $operatingSystem;
                }
            }

            $modelTypes = [];
            foreach($product->Queries->Types->Type as $type)
            {
                $modelTypes[] = (string)$type;
            }

            if(!isset($this->computerModels[$systemId]))
            {
                $this->computerModels[$systemId] = [
                    "product_family" => $attributes["family"],
                    "name" => $modelName,
                    "smbios" => (string)$product->Queries->Smbios,
                    "types" => $modelTypes,
                    "supported_operating_systems" => [$operatingSystem]
                ];
            }
            else
            {
                if(!in_array($operatingSystem, $this->computerModels[$systemId]["supported_operating_systems"]))
                    $this->computerModels[$systemId]["supported_operating_systems"][] = $operatingSystem;
            }
        }
    }

    public function processDriverPacks()
    {
        Log::info("Process " . count($this->driverPacks) . " driver packs");

        foreach($this->driverPacks as $packageId => $data)
        {
            if(isset($data["_supported_model_names"]))
            {
                if(!str_contains($data["category"], "WinPE"))
                    $data["name"] = implode("/", $data["_supported_model_names"])." Driver Pack";
                else
                    $data["name"] = "WinPE Driver Pack";

                unset($data["_supported_model_names"]);
            }

            LenovoDriverPackage::updateOrCreate(
                [
                    "package_id" => (string)$packageId // sql query bugs out if not explicitly cast to string
                ], $data
            );
        }
    }

    public function processCatalog()
    {
        Log::info("Starting processing of lenovo catalog");

        $this->getLocalCatalog();

        $this->parseCatalog();
        $this->processComputerModels();
        $this->processDriverPacks();

        Log::info("Finished processing lenovo catalog");
    }
}
