<?php

namespace Klepak\DriverManagement\Models\Dell;

use Klepak\DriverManagement\Models\VendorComputerModel;

class DellComputerModel extends VendorComputerModel
{
    protected $guarded = [];
    public $timestamps = false;

    public $incrementing = false;

    public function getCertificationInfo()
    {
        return [
            "name" => $this->name
        ];
    }

    public function driverPacks($operatingSystem, $osBuild = null)
    {
        $osCode = str_replace(" ", "", $operatingSystem);

        $driverPacks = DellDriverPack::where("supported_systems", "like", "%{$this->system_id}%")->where("supported_operating_systems", "like", "%$osCode%")->get();

        return $driverPacks;
    }

    public function softwareDrivers($operatingSystem, $osBuild = null)
    {
        $log = \Klepak\DriverManagement\Controllers\VendorCatalog\Dell\DellCatalogPcController::log();

        if($operatingSystem == "Windows 10")
        {
            $osCode = "W10P4";
        }
        else
        {
            $log->error("Os code for $operatingSystem not defined");
            return false;
        }

        $softwareComponents = DellSoftwareComponent::where("supported_systems", "like", "%{$this->system_id}%")->where("supported_operating_systems", "like", "%$osCode%")->get();

        $latestSoftwareComponents = [];
        foreach($softwareComponents as $softwareComponent)
        {
            $packageBasename = DellSoftwareComponent::getPackageBaseName($softwareComponent["name"]);

            if(!isset($latestSoftwareComponents[$packageBasename]))
            {
                $log->info("Init $packageBasename");
                $latestSoftwareComponents[$packageBasename] = $softwareComponent;
            }
            else
            {
                if($softwareComponent->dell_version > $latestSoftwareComponents[$packageBasename]->dell_version)
                {
                    $log->info("Newer $packageBasename: {$softwareComponent->dell_version}");
                    $latestSoftwareComponents[$packageBasename] = $softwareComponent;
                }
            }
        }

        $softwareComponentCollection = [];
        foreach($latestSoftwareComponents as $basename => $softwareComponent)
        {
            $softwareComponentCollection[] = $softwareComponent;
        }

        return collect($softwareComponentCollection);
    }
}
