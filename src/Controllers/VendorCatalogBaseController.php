<?php

namespace Klepak\DriverManagement\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use Symfony\Component\Process\Process;

use Log;

/**
 * @resource VendorCatalog
 *
 * Base controller for Vendor Catalog
 */
class VendorCatalogBaseController
{
    public $catalogSourceUrl = null;

    protected $localCatalogRelativePath = null;

    public $localCatalog = null;

    protected static $storagePath = null;

    /* public function triggerUpdateJob()
    {
        $job = new \App\Jobs\Hardware\CheckForVendorCatalogUpdates();

        return $this->trackDispatch($job);
    } */

    public static function getStoragePath() {
        $storagePath = storage_path("app\\".static::$storagePath);

        if(!file_exists($storagePath))
            mkdir($storagePath, 0777, true);

        return $storagePath;
    }

    public function downloadFile($url)
    {
        $storagePath = $this->getStoragePath();

        Log::info("Downloading file $url");

        $filePath = $storagePath."/".basename($url);

        $client = new Client(); //GuzzleHttp\Client
        try
        {
            $lastProgress = 0;

            $result = $client->request("GET", $url, [
                "sink" => $filePath,
                'progress' => function ($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) use (&$lastProgress) {
                    if($dl_total_size == 0)
                        $progress = 0;
                    else
                        $progress = floor($dl_size_so_far/$dl_total_size*100);

                    consoleProgressBar($dl_size_so_far, $dl_total_size, 'Downloading', 'K');

                    if($lastProgress != $progress)
                    {
                        Log::debug("Download progress: {$progress}%", [
                            'dl_total_size' => $dl_total_size,
                            'dl_size_so_far' => $dl_size_so_far,
                        ]);

                        $lastProgress = $progress;
                    }
                }
            ]);

            Log::info("Download success", ["filePath" => $filePath]);

            return $filePath;
        }
        catch(GuzzleException $e)
        {
            Log::error("Download error", ["Exception" => $e]);

            return false;
        }
    }

    public function downloadCatalog() {
        Log::info("Starting download of catalog");
        return $this->downloadFile($this->catalogSourceUrl);
    }

    public function getLocalCatalog()
    {
        if($this->localCatalog !== null)
            return $this->localCatalog;

        $localCatalogXmlPath = $this->getStoragePath()."\\".$this->localCatalogRelativePath;

        if(!file_exists($localCatalogXmlPath))
        {
            return false;
        }
        else
        {
            Log::info("Get local catalog", ["path" => $localCatalogXmlPath]);
            $this->localCatalog = new \SimpleXMLElement($localCatalogXmlPath, null, true);
        }

        return $this->localCatalog;
    }

    public function isLocalCatalogOutdated() {
        Log::error("Not implemented");
        return true;
    }

    public static function checkForCatalogUpdates() {
        $instance = new static;

        if($instance->isLocalCatalogOutdated())
        {
            $catalogPath = $instance->downloadCatalog();

            if($catalogPath !== false)
            {
                $extractedPath = $instance->extractCatalog($catalogPath);
                if($extractedPath !== false)
                {
                    $instance->processCatalog();
                }
            }
        }
        else
        {
            Log::info("No update available");
        }
    }

    public function processCatalog()
    {
        Log::error("Not implemented");
        return false;
    }

    public function extractCatalog($catalogPath)
    {
        Log::error("Not implemented");
        return false;
    }

    public function extractCab($cabPath)
    {
        Log::info("Try to extract $cabPath");

        $extractFileName = str_replace(".cab", ".xml", basename($cabPath));
        $extractPath = $this->getStoragePath()."/$extractFileName";

        $process = new Process("expand $cabPath -F:* $extractPath");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            return false;
        }

        return $extractPath;
    }
}
