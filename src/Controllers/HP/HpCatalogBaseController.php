<?php

namespace Klepak\DriverManagement\Controllers\HP;

use Klepak\DriverManagement\Controllers\VendorCatalogBaseController;

use Log;

/**
 * @resource HpCatalog
 *
 * Base controller for HP catalog
 */
class HpCatalogBaseController extends VendorCatalogBaseController
{
    protected static $storagePath = "drvmgmt\\catalog\\hp";

    protected $updateCatalogBaseKey = null;
    protected $catalogBaseKey = null;
    protected $catalogVersionAttributeName = null;

    protected $catalogUpdateXmlUrl = null;


    public function downloadUpdateXml() {
        Log::info("Starting download of update xml");
        return $this->downloadFile($this->catalogUpdateXmlUrl);
    }

    public function isLocalCatalogOutdated() {
        Log::info("Checking for catalog updates");

        $updateXmlPath = $this->downloadUpdateXml();
        if($updateXmlPath !== false)
        {
            Log::info("Processing update xml");
            $this->catalogUpdateXml = new \SimpleXMLElement($updateXmlPath, null, true);

            $updateCatalogBaseKey = $this->updateCatalogBaseKey;
            $catalogBaseKey = $this->catalogBaseKey;

            $updateAttributes = ((array)$this->catalogUpdateXml->$updateCatalogBaseKey->attributes())["@attributes"];
            $this->catalogSourceUrl = str_replace("ftp://", "http://", (string)$this->catalogUpdateXml->$updateCatalogBaseKey->CatalogUrl);

            $localCatalog = $this->getLocalCatalog();

            if($localCatalog === false)
            {
                Log::info("No local catalog found");
                return true;
            }

            $localCatalogAttributes = ((array)$localCatalog->$catalogBaseKey->attributes())["@attributes"];

            if($localCatalogAttributes[$this->catalogVersionAttributeName] < $updateAttributes[$this->catalogVersionAttributeName])
            {
                Log::info("Found update", ["current_version" => $localCatalogAttributes[$this->catalogVersionAttributeName], "new_version" => $updateAttributes[$this->catalogVersionAttributeName]]);

                return true;
            }
        }
        else
        {
            Log::info("Unable to download update xml");
        }

        return false;
    }
}
