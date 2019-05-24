<?php

namespace Klepak\DriverManagement\Models\Vendor\Lenovo;

use Klepak\DriverManagement\Models\VendorComputerModel;
use Exception;

class LenovoComputerModel extends VendorComputerModel
{
    protected $guarded = [];
    public $timestamps = false;

    public $incrementing = false;

    protected $casts = [
        "types" => "array",
        "supported_operating_systems" => "array",
    ];

    public static function getOsCode($operatingSystem)
    {
        switch($operatingSystem)
        {
            case "Windows 10":
                return "win10";
                break;

            default:
                throw new Exception("Os code for os $operatingSystem not defined");
        }
    }

    public function driverPack($operatingSystem, $osBuild = null)
    {
        $osCode = static::getOsCode($operatingSystem);

        $allDriverPacks = LenovoDriverPackage::where("supported_models", "like", "%{$this->system_id}%")
            ->where("supported_operating_systems", "like", "%$osCode%")
            ->where("type", "driver_pack")
            ->where("category", "sccm")
            ->get();

        if($allDriverPacks->count() > 1)
            throw new Exception('More than 1 driver pack matched');

        $driverPack = $allDriverPacks->first();
        $driverPack->osBuild = $osBuild;

        return $driverPack;
    }

    public function softwareDrivers($operatingSystem, $osBuild = false)
    {
        $osCode = static::getOsCode($operatingSystem);

        $allDriverPacks = LenovoDriverPackage::where("supported_models", "like", "%{$this->system_id}%")
            ->where("supported_operating_systems", "like", "%$osCode%")
            ->where("type", "hardware_app")
            ->get();

        return $allDriverPacks;
    }
}
