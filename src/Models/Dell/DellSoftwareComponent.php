<?php

namespace Klepak\DriverManagement\Models\Dell;

class DellSoftwareComponent extends DellBasePackage
{
    protected $primaryKey = "identifier";

    protected $casts = [
        "supported_devices" => "array",
        "supported_operating_systems" => "array",
        "supported_operating_system_languages" => "array",
        "supported_systems" => "array",
        "reboot_required" => "boolean"
    ];

    public function hardwareDevices() {
        return DellHardwareDevice::where("component_id", $this->supported_devices)->get();
    }

    public static function getPackageBaseName($packageName)
    {
        $packageNameSegments = explode(",", $packageName);
        $packageNameSegmentsCount = count($packageNameSegments);

        unset($packageNameSegments[$packageNameSegmentsCount-1]);
        unset($packageNameSegments[$packageNameSegmentsCount-2]);

        return implode(",", $packageNameSegments);
    }

    public function extract()
    {
        return $this->download();
    }
}
