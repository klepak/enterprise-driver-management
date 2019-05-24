<?php

namespace Klepak\DriverManagement\Models\DriverSet;

use Illuminate\Database\Eloquent\Model;
use Klepak\DriverManagement\Models\VendorComputerModel;

class ComputerModel extends Model
{
    public static function fromVendorModel(VendorComputerModel $vendorModel, $operatingSystem = "Windows 10", $osBuild = null)
    {
        $driverPack = $vendorModel->driverPack('Windows 10', 1803);
    }

    public function driverSets()
    {
        return $this->belongsToMany(DriverSet::class);
    }

    public function vendorModels()
    {
        return $this->morphTo();
    }
}
