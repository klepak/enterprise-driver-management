<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\Dell;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Klepak\DriverManagement\Controllers\VendorCatalog\VendorCatalogBaseController;
use Storage;
use Log;
use Klepak\DriverManagement\Models\Dell\DellComputerModel;
use Klepak\DriverManagement\Models\Dell\DellOperatingSystem;
use Klepak\ConsoleProgressBar\ConsoleProgressBar;

/**
 * @resource DellCatalog
 *
 * Base controller for Dell catalog
 */
class DellCatalogBaseController extends VendorCatalogBaseController
{
    protected static $storagePath = "drvmgmt\\catalog\\dell";
    protected static $ftpRoot = "http://ftp.dell.com/";

    protected $catalogRelativeFtpPath = null;

    private $remoteLastChanged = null;

    private $allComputerModels = [];
    private $allHardwareDevices = [];

    public function __construct()
    {
        $this->catalogSourceUrl = static::$ftpRoot.$this->catalogRelativeFtpPath;
    }

    public function getCatalogIdentifier()
    {
        $pathParts = pathinfo(basename($this->localCatalogRelativePath));

        return $pathParts["filename"];
    }

    public function downloadCatalog() {
        $parent = parent::downloadCatalog();

        if($parent !== false)
        {
            if($this->remoteLastChanged !== null)
                Storage::put(static::$storagePath."\\{$this->getCatalogIdentifier()}-LastChanged.txt", $this->remoteLastChanged);
        }

        return $parent;
    }

    public function isLocalCatalogOutdated()
    {
        Log::info("Checking for catalog updates");

        $lastChangedFilePath = static::$storagePath."\\{$this->getCatalogIdentifier()}-LastChanged.txt";

        try
        {
            $localLastChanged = Storage::get($lastChangedFilePath);
        }
        catch(FileNotFoundException $e)
        {
            Log::info("No last changed file found");
            $localLastChanged = false;
        }

        // check last changed date on ftp server
        $conn_id = ftp_connect("ftp.dell.com");
        $login_result = ftp_login($conn_id, "anonymous", "");
        $remoteLastChanged = ftp_mdtm($conn_id, $this->catalogRelativeFtpPath);
        ftp_close($conn_id);

        if($remoteLastChanged > $localLastChanged)
        {
            Log::info("Catalog update available", ["remote_version" => $remoteLastChanged, "local_version" => $localLastChanged]);
            $this->remoteLastChanged = $remoteLastChanged;

            return true;
        }

        return false;
    }

    public function extractCatalog($catalogPath)
    {
        return $this->extractCab($catalogPath);
    }

    public function processComputerModels()
    {
        $progress = (new ConsoleProgressBar)
            ->max(count($this->allComputerModels))
            ->message('Processing computer models');

        Log::info("Processing " . count($this->allComputerModels) . " computer models");
        if(!empty($this->allComputerModels))
        {
            $i = 0;
            foreach($this->allComputerModels as $systemId => $data)
            {
                $progress
                    ->update(++$i);

                DellComputerModel::updateOrCreate([
                    "id" => (string)$systemId,
                    "system_id" => (string)$systemId,
                ], $data);
            }

            $progress->completed();
        }
    }

    public function processOperatingSystems()
    {
        $progress = (new ConsoleProgressBar)
            ->max(count($this->allOperatingSystems))
            ->message('Processing operating systems');

        Log::info("Processing " . count($this->allOperatingSystems) . " operating systems");
        if(!empty($this->allOperatingSystems))
        {
            $i = 0;
            foreach($this->allOperatingSystems as $osCode => $data)
            {
                $progress
                    ->update(++$i);

                DellOperatingSystem::updateOrCreate([
                    "os_code" => $osCode
                ], $data);
            }

            $progress->completed();
        }
    }

    public function parseSupportedOperatingSystems($supportedOperatingSystems)
    {
        $osCodes = [];
        $osLanguages = [];

        if(isset($supportedOperatingSystems->OperatingSystem))
        {
            foreach($supportedOperatingSystems->OperatingSystem as $operatingSystem)
            {
                $attributes = ((array)$operatingSystem->attributes())["@attributes"];

                $osCode = $attributes["osCode"];

                $supportedLanguages = [];
                foreach($operatingSystem->SupportedLanguages->Language as $supportedLanguage)
                {
                    $supportedLanguages[] = (string)$supportedLanguage;
                }

                $osCodes[] = $osCode;
                $osLanguages[] = $supportedLanguages;

                if(!isset($this->allOperatingSystems[$osCode]))
                {
                    $this->allOperatingSystems[$osCode] = [
                        "os_vendor" => $attributes["osVendor"],
                        "major_version" => $attributes["majorVersion"],
                        "minor_version" => $attributes["minorVersion"],
                        "sp_major_version" => $attributes["spMajorVersion"],
                        "sp_minor_version" => $attributes["spMinorVersion"],
                        "os_arch" => $attributes["osArch"],
                        "description" => (string)$operatingSystem->Display,
                    ];
                }
            }
        }

        return ["os_codes" => $osCodes, "os_languages" => $osLanguages];
    }

    public function parseSupportedSystems($supportedSystems)
    {
        $system_ids = [];

        if($supportedSystems->Brand == null)
            return [];

        foreach($supportedSystems->Brand as $brand)
        {
            $brandName = (string)$brand->Display;

            foreach($brand->Model as $model)
            {
                $modelAttributes = ((array)$model->attributes())["@attributes"];
                $systemId = $modelAttributes["systemID"];

                $system_ids[] = $systemId;

                if(!isset($this->allComputerModels[$systemId]))
                {
                    $this->allComputerModels[$systemId] = [
                        "name" => trim(str_replace("   ", " ", "$brandName ".((string)$model->Display)))
                    ];
                }
            }
        }

        return $system_ids;
    }
}
