<?php

namespace Klepak\DriverManagement\Models\Lenovo;

use Klepak\DriverManagement\Models\VendorSoftwarePackage;
use Exception;
use Symfony\Component\Process\Process;

class LenovoDriverPackage extends VendorSoftwarePackage
{
    protected $casts = [
        "supported_models" => "array",
        "supported_operating_systems" => "array",
    ];

    protected $primaryKey = "package_id";

    public function getSilentInstallCommand()
    {
        return $this->install_cmd;
    }

    public function getDownloadUrl()
    {
        return str_replace("https://", "http://", $this->download_url);
    }

    public function getHashProperty()
    {
        return false;
    }

    public function getDownloadFileSize()
    {
        return false;
    }

    public function getIdentifier()
    {
        return $this->package_id;
    }

    public function getDirectDownloadLink()
    {
        $parent = parent::download();

        if($parent !== false)
        {
            $pageContent = file_get_contents($parent);

            echo $pageContent;
            return;

            if(preg_match("/(http[s]?)(:\/\/)([^\s,]+.exe)(?=\")/", $pageContent, $matches))
            {
                $downloadUrl = $matches[0];

                return $downloadUrl;
            }
            else
            {
                throw new Exception("Unable to parse download link");
            }
        }

        return false;
    }

    public function download($url = false)
    {
        return parent::download($this->getDirectDownloadLink());
    }

    public function extract()
    {
        $downloadPath = $this->download();

        if($downloadPath !== false)
        {
            $packageBaseName = basename($downloadPath);
            $pathParts = pathinfo($packageBaseName);
            $packageFileName = $pathParts["filename"];

            $extractPath = storage_path("app\\extract\\$packageFileName");
            $extractParams = "/VERYSILENT /DIR=" . '"' . $extractPath . '"' . ' /Extract="Yes"';

            Log::info("Extracting $packageBaseName");

            $process = new Process($downloadPath." ".$extractParams);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                return false;
            }

            return $extractPath;
        }
    }
}
