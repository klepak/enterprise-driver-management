<?php

namespace Klepak\DriverManagement\Models\Vendor\HP;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use Log;
use Klepak\DriverManagement\Models\VendorSoftwarePackage;

class HpSoftpaq extends VendorSoftwarePackage
{
    protected $casts = [
        "supported_languages" => "array",
        "supported_os" => "array",
        "ssm_compliant" => "boolean",
        "dpb_compliant" => "boolean",
    ];

    protected $primaryKey = "id";

    public function getSilentInstallCommand()
    {
        return $this->silent_install;
    }

    public function getDownloadUrl()
    {
        return str_replace_first("ftp://", "http://", $this->url);
    }

    public function getHashProperty()
    {
        return $this->md5;
    }

    public function getDownloadFileSize()
    {
        return $this->size/1024;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    //

    public function checkFileHash($hash)
    {
        if(parent::checkFileHash($hash))
        {
            $this->downloadCva();

            return true;
        }

        return false;
    }

    public function downloadCva()
    {
        $url = str_replace_first("ftp://", "http://", $this->url);
        $url = str_replace_last(".exe", ".cva", $url);

        $storagePath = storage_path("app\\download");

        $outFile = $storagePath."/".str_replace_last(".exe", ".cva", basename($url));

        if(file_exists($outFile))
        {
            Log::info("Cva file already downloaded");
            return $outFile;
        }

        Log::info("Start download of sp{$this->id}.cva", ["url" => $url, "outFile" => $outFile]);
        $client = new Client(); //GuzzleHttp\Client
        try
        {
            $result = $client->request("GET", $url, [
                "sink" => $outFile
            ]);

            return $outFile;
        }
        catch(GuzzleException $e)
        {
            Log::error("Download error", ["Exception" => $e]);
        }

        return false;
    }

    public function extract()
    {
        $downloadPath = $this->download();

        if($downloadPath !== false)
        {
            $zipExecutablePath = storage_path("exe\\7z");
            $zipExecutable = $zipExecutablePath."\\7z.exe";

            $extractPath = storage_path("app\\extract\\sp{$this->id}");

            Log::info("Extracting sp{$this->id}");

            $process = new \Symfony\Component\Process\Process("$zipExecutable x $downloadPath -o\"$extractPath\" -aoa");
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                return false;
            }

            return $extractPath;
        }
    }
}
