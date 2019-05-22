<?php

namespace Klepak\DriverManagement\Models\HP;

use Klepak\DriverManagement\Controllers\VendorCatalog\HP\HpProductCatalogController;

use Log;
use Klepak\DriverManagement\Models\VendorComputerModel;
use Exception;

class HpComputerModel extends VendorComputerModel
{
    protected $casts = [
        "softpaq_ids" => "array",
        "supported_os_ids" => "array",
        "system_id" => "array",
    ];

    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    public function getName()
    {
        return $this->short_name;
    }

    public function getCertificationInfo()
    {
        return [
            "name" => $this->short_name,
            "long_name" => $this->name,
        ];
    }

    public function driverPack($operatingSystem, $osBuild = null)
    {
        $osNameStr = "{$operatingSystem} 64-bit";

        if($osBuild !== null)
            $osNameStr .= ", {$osBuild}";
        else
            $osNameStr .= "%";

        $allDriverPacks = HpDriverPack::where(function($sub) {
            foreach($this->system_id as $systemId)
            {
                $sub->orWhere("system_id", 'like', "%\"{$systemId}\"%");
            }
        })
            ->where("os_name", "like", $osNameStr)
            ->get()
            ->unique('os_name');

        if($osBuild == null && $allDriverPacks->count() > 0)
            throw new Exception("Found multiple driver packs for same build");

        $driverPack = $allDriverPacks->sortByDesc('os_name')->first();

        return HpSoftpaq::find(str_replace("sp", "", $driverPack->softpaq_id));
    }

    // TODO: pick latest os build
    public function softwareDrivers($operatingSystem, $osBuild = 1709)
    {
        $osStr = "%$operatingSystem";
        if($osBuild !== null)
            $osStr .= " version $osBuild%";
        else
            $osStr .= "%";

        $modelData = HpProductCatalogController::getModelSpecificData($this->id);
        $osData = HpOperatingSystem::where("name", "like", $osStr)->get();

        if(count($osData) > 1)
        {
            Log::error("More than 1 os matched", ["operatingSystem" => $operatingSystem, "osBuild" => $osBuild]);
            return false;
        }

        $osData = $osData->first();

        if(!isset($modelData[$osData["id"]]))
        {
            Log::error("No model data on provided os id", ["osId" => $osData["id"], "osStr" => $osStr]);
            return false;
        }

        $modelData = $modelData[$osData["id"]];

        $langId = 13; // 13 = English - International

        if(!isset($modelData[$langId]))
        {
            Log::error("No model data on provided lang id", ["osId" => $osData["id"], "langId" => $langId]);
            return false;
        }

        $softpaqIds = $modelData[$langId];

        $softpaqs = HpSoftpaq::whereIn("id", $softpaqIds)->where("ssm_compliant", true)->get();

        $ignorePackages = [
            "System Default Settings for Windows 10",
            "System Default Settings for Windows 8.1",
            "HP Image Assistant",
            "Intel Management Engine (ME) Firmware Update Tool",
            "HP PC Hardware Diagnostics UEFI",
            "HP Client Security Manager",
            "Vigyanlabs IPM+ Software",
            "Vigyanlabs IPM+  Software",
            "HP Client Security Manager",
            "HP Notifications",
            "HP Notifications Application",
            "Intel I219 NIC Drivers for DTO Microsoft Win 10 -64bit",
            "HP Recovery Manager Update",
            "HP Collaboration PC",
            "Foxit PhantomPDF Express for HP",
            "HP Conferencing Keyboard Application",
            "HP MIKClient",
            "HP Sure Connect",
            "HP Device Access Manager",
            "HP Mobile Connect Metadata",
            "HP MAC Address Manager",
            "Intel I219LM/V Gigabit Ethernet Driver for Microsoft Windows",
            "HP Velocity",
            "HP Workwise",
            "HP WorkWise",
            "HP WorkWise Service",
            "Intel Wireless Display Software",
            "HP Network Priority",
            "Intel WiDi Gen_6 Software",
            "HP System Software Manager (SSM)",
            "HP hs3210 HSPA+ Mobile Broadband Drivers",
            "Broadcom Ethernet Controller Drivers -64bit (BNB)",
            "CyberLink Power2Go (BnB)",
            "HP Sure Click",
            "HP Drive Encryption",
            "HP Wireless Hotspot",
            "HP Computrace",
            "HP File Sanitizer",
            "HP Sure Recover",
            "HP Sure Run",
            "Cyberlink Power2Go Software",
            "HP BIOS Config Utility (BCU)"
        ];

        $softpaqAliases = [
            "Broadcom Wireless LAN" => [
                " Driver",
                " Dual Band Drivers"
            ],
            "Intel Chipset Support" => [
                " for Windows"
            ],
            "Intel Graphics Driver" => [
                "s",
                " - 64b"
            ],
            "Intel Wireless LAN Driver" => [
                "s"
            ],
            "Synaptics * Fingerprint" => [
                " Driver",
                " Sensor Driver"
            ],
            "NXP *NPC100 *Proximity" => [
                " Drivers"
            ],
            "AMD*Video" => [
                " Driver*"
            ],
            "Intel Bluetooth Driver" => [
                " (Windows 10)"
            ],
            "Intel Network Connections Drivers Release" => [
                "*for Windows 10 64-bit"
            ],
            "Intel Corporate Management Engine (ME)" => [
                " Firmware",
                " Firmware Component"
            ],
            "Conexant High Definition Audio" => [
                " Driver",
                " Driver for DT (Sustaining)"
            ],
            "Realtek RTL8723BE Bluetooth" => [
                " 4.0 Driver",
                " Driver"
            ],
            "Intel Rapid Storage Technology" => [
                "",
                " driver"
            ]
        ];

        $latestSoftpaqs = [];
        foreach($softpaqs as $softpaq)
        {
            $name = $softpaq["name"];
            foreach($softpaqAliases as $parentAlias => $subAliases)
            {
                if(str_is($parentAlias."*", $name))
                {
                    foreach($subAliases as $subAlias)
                    {
                        if(str_is($parentAlias.$subAlias, $name))
                        {
                            Log::info("$name belongs to $parentAlias");
                            $name = $parentAlias;
                        }
                    }
                }
            }

            if(in_array($name, $ignorePackages))
            {
                Log::info("Ignoring $name");
                continue;
            }

            if(str_is("*System BIOS*", $name))
            {
                Log::info("Ignoring BIOS package $name");
                continue;
            }

            if(!isset($latestSoftpaqs[$name]))
            {
                Log::info("Initial version of $name");
                $latestSoftpaqs[$name] = $softpaq;
            }
            else
            {
                $currentVersion = $softpaq["version"];
                $latestVersion = $latestSoftpaqs[$name]["version"];

                Log::info("$name: Current version: $currentVersion, latest: $latestVersion");

                if($currentVersion > $latestVersion)
                {
                    Log::info("Replace!");
                    $latestSoftpaqs[$name] = $softpaq;
                }
            }
        }

        $softpaqCollection = [];
        foreach($latestSoftpaqs as $name => $softpaq)
        {
            $softpaqCollection[] = $softpaq;
        }
        $softpaqCollection = collect($softpaqCollection);

        return $softpaqCollection;
    }
}
