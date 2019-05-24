<?php

namespace Klepak\DriverManagement\Models\Lenovo;

use Klepak\DriverManagement\Models\VendorSoftwarePackage;
use Exception;
use Symfony\Component\Process\Process;
use Klepak\PhpJsWebRequest\PhpJsWebRequest;
use Str;

class LenovoDriverPackage extends VendorSoftwarePackage
{
    public $osBuild = null;

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
        $pageContent = (new PhpJsWebRequest)->get($this->getDownloadUrl());

        if(preg_match_all("/(http[s]?)(:\/\/)([^\s,]+.exe)(?=\")/", $pageContent, $matches))
        {
            $downloadLinks = collect($matches[0])->unique()->sort();

            if($this->osBuild == null)
                return $downloadLinks->last();

            $downloadLinks = $downloadLinks->filter(function($item) {
                return Str::is("*_{$this->osBuild}_*", $item);
            });

            if($downloadLinks->count() > 1)
                throw new Exception('More than one link matched');

            return $downloadLinks->first();
        }
        else
        {
            throw new Exception("Unable to parse download link");
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
