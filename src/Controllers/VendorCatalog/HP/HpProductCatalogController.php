<?php

namespace Klepak\DriverManagement\Controllers\VendorCatalog\HP;

use Klepak\DriverManagement\Models\HP\HpComputerModel;
use Klepak\DriverManagement\Models\HP\HpOperatingSystem;
use Klepak\DriverManagement\Models\HP\HpLanguage;
use Klepak\DriverManagement\Models\HP\HpSoftpaq;
use Klepak\DriverManagement\Models\HP\HpHardware;

use Log;

/**
 * @resource HpProductCatalog
 *
 * Controller for HP product catalog
 */
class HpProductCatalogController extends HpCatalogBaseController
{
    protected $catalogUpdateXmlUrl = "http://ftp.hp.com/pub/caps-softpaq/ProductCatalogUpdate.xml";
    protected $localCatalogRelativePath = "ProductCatalog\\modelcatalog.xml";

    protected $updateCatalogBaseKey = "ProductCatalog";
    protected $catalogBaseKey = "ProductCatalog";
    protected $catalogVersionAttributeName = "CatalogVersion";

    public static function getModelSpecificData($modelId) {
        $modelSpecificFilePath = static::getStoragePath()."/ProductCatalog/$modelId.xml";

        if(file_exists($modelSpecificFilePath))
        {
            Log::info("Parsing model specific catalog", ["filePath" => $modelSpecificFilePath]);

            $modelData = [];

            $modelSpecificCatalog = new \SimpleXMLElement($modelSpecificFilePath, null, true);
            foreach($modelSpecificCatalog->xpath("//OS") as $productOs)
            {
                $attributes = ((array)$productOs->attributes())["@attributes"];

                $osId = $attributes["Id"];

                if(!isset($modelData[$osId]))
                    $modelData[$osId] = [];

                foreach($productOs->Lang as $productOsLang)
                {
                    $attributes = ((array)$productOsLang->attributes())["@attributes"];
                    $langId = $attributes["Id"];

                    if(!isset($modelData[$osId][$langId]))
                        $modelData[$osId][$langId] = [];

                    $softpaqIds = [];
                    foreach($productOsLang->SP as $productOsLangSoftpaq)
                    {
                        $attributes = ((array)$productOsLangSoftpaq->attributes())["@attributes"];
                        $modelData[$osId][$langId][] = $attributes["Id"];
                    }
                }
            }

            return $modelData;
        }
        else
        {
            Log::warning("Could not locate model specific xml for ($modelId)");
        }

        return false;
    }

    public function processProductModels()
    {
        Log::info("Process product models from model catalog");

        foreach($this->getLocalCatalog()->xpath("//ProductModel") as $productModel)
        {
            $attributes = ((array)$productModel->attributes())["@attributes"];

            $id = $attributes["Id"];
            $name = (string)$productModel->Name;

            HpComputerModel::updateOrCreate(
                ["id" => $id],
                [
                    "name" => $name,
                    "short_name" => (string)$productModel->ShortName,
                    "system_id" => explode(",", (string)$productModel->SystemID),
                    "dpb_compliant" => (string)$productModel->DPBCompliant,
                    "supported_os_ids" => explode(", ", (string)$productModel->SupportedOSID),
                ]
            );
        }
    }

    public function processOperatingSystems()
    {
        Log::info("Process operating systems from model catalog");

        foreach($this->getLocalCatalog()->xpath("//OperatingSystem") as $operatingSystem)
        {
            $attributes = ((array)$operatingSystem->attributes())["@attributes"];
            $osId = $attributes["Id"];

            HpOperatingSystem::updateOrCreate(
                ["id" => $osId],
                [
                    "name" => (string)$operatingSystem->Name,
                    "ms_name" => (string)$operatingSystem->MSName,
                    "ssm_name" => (string)$operatingSystem->SSMName,
                    "os_base" => (string)$operatingSystem->OSBase
                ]
            );
        }
    }

    public function processLanguages()
    {
        Log::info("Process languages from model catalog");

        foreach($this->getLocalCatalog()->xpath("//Language") as $language)
        {
            $attributes = ((array)$language->attributes())["@attributes"];
            $langId = $attributes["Id"];

            HpLanguage::updateOrCreate(
                ["id" => $langId],
                [
                    "name" => (string)$language->Name,
                    "lcid" => (string)$language->LCID
                ]
            );
        }
    }

    public function processSoftpaqs()
    {
        Log::info("Process softpaqs from model catalog");

        $softpaqs = $this->getLocalCatalog()->xpath("//Softpaq");
        $count = count($softpaqs);
        $i = 0;

        foreach($softpaqs as $softpaq)
        {
            $attributes = ((array)$softpaq->attributes())["@attributes"];
            $softpaqId = $attributes["Id"];

            consoleProgressBar(++$i, $count, "Processing softpaqs (sp$softpaqId)");

            HpSoftpaq::updateOrCreate(
                ["id" => $softpaqId],
                [
                    "name" => (string)$softpaq->Name,
                    "version" => (string)$softpaq->Version,
                    "category" => (string)$softpaq->Category,
                    "date_released" => (string)$softpaq->DateReleased,
                    "purpose" => (string)$softpaq->Purpose,
                    "url" => (string)$softpaq->Url,
                    "size" => (string)$softpaq->Size,
                    "supported_languages" => explode(", ", (string)$softpaq->SupportedLanguages),
                    "supported_os" => explode(", ", (string)$softpaq->SupportedOS),
                    "cva_file_url" => (string)$softpaq->CvaFileUrl,
                    "release_notes_url" => (string)$softpaq->ReleaseNotesUrl,
                    "silent_install" => (string)$softpaq->SilentInstall,
                    "ssm_compliant" => ((string)$softpaq->SSMCompliant == "true") ? true : false,
                    "dpb_compliant" => ((string)$softpaq->DPBCompliant == "true") ? true : false,
                    "md5" => (string)$softpaq->MD5,
                    "vendor_name" => (string)$softpaq->VendorName,
                    "vendor_version" => (string)$softpaq->VendorVersion,
                    "col_id" => (string)$softpaq->ColID,
                    "item_id" => (string)$softpaq->ItemID,
                ]
            );
        }
    }

    public function processHardware()
    {
        Log::info("Process hardware from model catalog");

        $hardwares = $this->getLocalCatalog()->xpath("//HW");
        $count = count($hardwares);
        $i = 0;

        foreach($hardwares as $hardware)
        {
            $hwId = (string)$hardware->HWID;

            consoleProgressBar(++$i, $count, 'Processing hardware');

            HpHardware::updateOrCreate(
                [
                    "hw_id" => $hwId,
                    "softpaq" => (string)$hardware->SP
                ]
            );
        }
    }

    public function processCatalog()
    {
        Log::info("Starting processing of product catalog");

        $this->processProductModels();
        $this->processOperatingSystems();
        $this->processLanguages();
        $this->processSoftpaqs();
        $this->processHardware();

        Log::info("Finished processing product catalog");
    }

    public function extractCatalog($catalogPath)
    {
        $storagePath = static::getStoragePath();
        $extractPath = $storagePath."/ProductCatalog";

        Log::info("Preparing to extract files from product catalog", ["catalogPath" => $catalogPath]);

        if(file_exists($extractPath))
        {
            Log::info("Extract path already exists, will empty folder");
            foreach(glob("{$extractPath}/*") as $file)
            {
                unlink($file);
            }
        }

        $zip = new \ZipArchive();

        if($zip->open($catalogPath) === true) {
            if($zip->extractTo($storagePath))
            {
                Log::info("Successfully extracted files to $storagePath");
                $zip->close();
                return $extractPath;
            }
            $zip->close();
        }

        Log::error("Failed to extract files to $storagePath");

        return false;
    }
}
