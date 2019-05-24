<?php

namespace Klepak\DriverManagement\Models\Vendor\Dell;

use Klepak\DriverManagement\Models\VendorSoftwarePackage;

class DellBasePackage extends VendorSoftwarePackage
{
    public function getSilentInstallCommand()
    {
        $fileName = basename($this->path);
        return "$fileName /s /i";
    }

    public function getDownloadUrl()
    {
        return "http://ftp.dell.com/{$this->path}";
    }

    public function getHashProperty()
    {
        return $this->hash_md5;
    }

    public function getDownloadFileSize()
    {
        return $this->size/1024;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    //

    public function operatingSystems()
    {
        return DellOperatingSystem::where("os_code", $this->supported_operating_systems)->get();
    }

    public function computerModels()
    {
        return DellComputerModel::where("system_id", $this->supported_systems)->get();
    }
}
