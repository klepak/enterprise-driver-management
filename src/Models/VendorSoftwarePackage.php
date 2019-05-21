<?php

namespace Klepak\DriverManagement\Models;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Log;

class VendorSoftwarePackage extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    public function getSilentInstallCommand()
    {
        throw new Exception("Not implemented");
    }

    public function getDownloadUrl()
    {
        throw new Exception("Not implemented");
    }

    public function getHashProperty()
    {
        throw new Exception("Not implemented");
    }

    public function getDownloadFileSize()
    {
        throw new Exception("Not implemented");
    }

    public function getIdentifier()
    {
        throw new Exception("Not implemented");
    }

    //

    public function checkFileHash($hash)
    {
        if($this->getHashProperty() === false)
        {
            $this->log()->warning("Hash checking disabled");
            return true;
        }
        else
        {
            if(strtolower($hash) == strtolower($this->getHashProperty()))
            {
                $this->log()->info("Hash verified");

                return true;
            }
            else
            {
                $this->log()->warning("Hash check failed");
            }
        }
    }

    public function download($url = false)
    {
        if($url === false)
            $url = $this->getDownloadUrl();

        $storagePath = storage_path("app\\download");
        $outFile = $storagePath."/".basename($url);
        $fileSize = $this->getDownloadFileSize();

        if(!file_exists($storagePath))
            mkdir($storagePath);

        if(file_exists($outFile) && $this->getHashProperty() !== false)
        {
            Log::info("File already exists, will verify hash", ["package_id" => basename($url)]);

            if($this->checkFileHash(md5_file($outFile)))
                return $outFile;
            else
                Log::info("File exists, but failed hash check -- will re-download");
        }

        Log::info("Start download of {$this->getIdentifier()}", ["url" => $url, "outFile" => $outFile, "fileSize" => $fileSize]);
        $client = new Client(); //GuzzleHttp\Client
        try
        {

            $result = $client->request("GET", $url, [
                "sink" => $outFile,
                "curl" => [
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]);

            if($this->checkFileHash(md5_file($outFile)))
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
        throw new Exception("Not implemented");
    }
}
