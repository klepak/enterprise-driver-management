<?php

namespace Klepak\DriverManagement\Models\Dell;

use Klepak\DriverManagement\Models\VendorComputerModel;
use Exception;
use Log;
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

    public function driverPack($operatingSystem, $osBuild = null)
    {
        $osCode = str_replace(" ", "", $operatingSystem);

        $driverPacks = DellDriverPack::where("supported_systems", "like", "%{$this->system_id}%")->where("supported_operating_systems", "like", "%$osCode%")->get();

        if($driverPacks->count() > 1)
            throw new Exception('Too many driver packages matched');

        return $driverPacks->first();
    }

    public function softwareDrivers($operatingSystem, $osBuild = null)
    {
        if($operatingSystem == "Windows 10")
        {
            $osCode = "W10P4";
        }
        else
        {
            Log::error("Os code for $operatingSystem not defined");
            return false;
        }

        $softwareComponents = DellSoftwareComponent::where("supported_systems", "like", "%{$this->system_id}%")->where("supported_operating_systems", "like", "%$osCode%")->get();

        $latestSoftwareComponents = [];
        foreach($softwareComponents as $softwareComponent)
        {
            $packageBasename = DellSoftwareComponent::getPackageBaseName($softwareComponent["name"]);

            if(!isset($latestSoftwareComponents[$packageBasename]))
            {
                Log::info("Init $packageBasename");
                $latestSoftwareComponents[$packageBasename] = $softwareComponent;
            }
            else
            {
                if($softwareComponent->dell_version > $latestSoftwareComponents[$packageBasename]->dell_version)
                {
                    Log::info("Newer $packageBasename: {$softwareComponent->dell_version}");
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
