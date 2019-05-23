<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\Dell;

use Log;
use Klepak\DriverManagement\Models\Dell\DellSoftwareComponent;
use Klepak\DriverManagement\Models\Dell\DellHardwareDevice;

/**
 * @resource DellCatalogPc
 *
 * Controller for DellCatalogPc
 */
class DellCatalogPcController extends DellCatalogBaseController
{
    protected $catalogRelativeFtpPath = "catalog/CatalogPC.cab";
    protected $localCatalogRelativePath = "CatalogPC.xml";

    public function processSoftwareComponents()
    {
        $softwareComponents = $this->getLocalCatalog()->xpath("//SoftwareComponent");

        Log::info("Processing " . count($softwareComponents) . " software components");

        foreach($softwareComponents as $softwareComponent)
        {
            $attributes = ((array)$softwareComponent->attributes())["@attributes"];
            $criticalityAttributes = ((array)$softwareComponent->Criticality->attributes())["@attributes"];
            $importantInfoAttributes = ((array)$softwareComponent->ImportantInfo->attributes())["@attributes"];

            $osData = $this->parseSupportedOperatingSystems($softwareComponent->SupportedOperatingSystems);

            DellSoftwareComponent::updateOrCreate(
                [
                    "identifier" => $attributes["identifier"],
                ],
                [
                    "package_id" => isset($attributes["packageID"]) ? $attributes["packageID"] : "",
                    "release_id" => isset($attributes["releaseID"]) ? $attributes["releaseID"] : "",
                    "hash_md5" => isset($attributes["hashMD5"]) ? $attributes["hashMD5"] : "",
                    "path" => isset($attributes["path"]) ? $attributes["path"] : "",
                    "date_time" => isset($attributes["dateTime"]) ? $attributes["dateTime"] : "",
                    "release_date" => isset($attributes["releaseDate"]) ? $attributes["releaseDate"] : "",
                    "vendor_version" => isset($attributes["vendorVersion"]) ? $attributes["vendorVersion"] : "",
                    "dell_version" => isset($attributes["dellVersion"]) ? $attributes["dellVersion"] : "",
                    "package_type" => isset($attributes["packageType"]) ? $attributes["packageType"] : "",
                    "reboot_required" => (isset($attributes["rebootRequired"]) && $attributes["rebootRequired"] == "true") ? true : false,
                    "size" => isset($attributes["size"]) ? $attributes["size"] : "",

                    "name" => (string)$softwareComponent->Name->Display,
                    "component_type" => (string)$softwareComponent->ComponentType->Display,
                    "description" => (string)$softwareComponent->Description->Display,
                    "category" => (string)$softwareComponent->Category->Display,

                    "supported_devices" => $this->parseSupportedDevices($softwareComponent->SupportedDevices),
                    "supported_operating_systems" => $osData["os_codes"],
                    "supported_operating_system_languages" => $osData["os_languages"],
                    "supported_systems" => $this->parseSupportedSystems($softwareComponent->SupportedSystems),

                    "info_url" => $importantInfoAttributes["URL"],
                    "criticality" => $criticalityAttributes["value"],
                    "criticality_display" => (string)$softwareComponent->Criticality->Display,
                    "msi_id" => (string)$softwareComponent->MSIID,
                ]
            );
        }
    }

    public function parseSupportedDevices($supportedDevices)
    {
        $devices = [];

        foreach($supportedDevices->Device as $device)
        {
            $attributes = ((array)$device->attributes())["@attributes"];
            $componentId = $attributes["componentID"];

            $deviceData = [
                "description" => (string)$device->Display,
                "component_id" => $componentId,
                "embedded" => $attributes["embedded"]
            ];

            if(isset($device->PCIInfo))
            {
                $pciInfoData = [];
                foreach($device->PCIInfo as $pciInfo)
                {
                    $pciAttributes = ((array)$pciInfo->attributes())["@attributes"];
                    $pciInfoData[] = [
                        "description" => (string)$pciInfo->Display,
                        "device_id" => $pciAttributes["deviceID"],
                        "vendor_id" => $pciAttributes["vendorID"],
                        "sub_device_id" => $pciAttributes["subDeviceID"],
                        "sub_vendor_id" => $pciAttributes["subVendorID"],
                    ];
                }

                $deviceData["pci_info"] = $pciInfoData;
            }

            if(isset($device->PnPInfo))
            {
                $pnpInfoData = [];
                foreach($device->PnPInfo as $pnpInfo)
                {
                    $pnpInfoData[] = [
                        "acpi_id" => (string)$pnpInfo->ACPIID,
                        "pnp_product_id" => (string)$pnpInfo->PnPProductID
                    ];
                }

                $deviceData["pnp_info"] = $pnpInfoData;
            }

            $devices[] = $componentId;

            if(!isset($this->allHardwareDevices[$componentId]))
                $this->allHardwareDevices[$componentId] = $deviceData;
        }

        return $devices;
    }

    public function processHardwareDevices()
    {
        Log::info("Processing " . count($this->allHardwareDevices) . " hardware devices");
        if(!empty($this->allHardwareDevices))
        {
            foreach($this->allHardwareDevices as $componentId => $data)
            {
                DellHardwareDevice::updateOrCreate([
                    "component_id" => $componentId
                ], $data);
            }
        }
    }

    public function processCatalog()
    {
        Log::info("Starting processing of CatalogPC");

        $this->getLocalCatalog();

        $this->processSoftwareComponents();
        $this->processComputerModels();
        $this->processHardwareDevices();
        $this->processOperatingSystems();

        Log::info("Finished processing CatalogPC");
    }
}
