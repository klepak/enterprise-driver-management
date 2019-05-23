<?php

namespace Klepak\DriverManagement\Models\Dell;

use Log;

class DellDriverPack extends DellBasePackage
{
    protected $primaryKey = "release_id";

    protected $casts = [
        "supported_operating_systems" => "array",
        "supported_operating_system_languages" => "array",
        "supported_systems" => "array"
    ];

    public function extract()
    {
        $downloadPath = $this->download();

        if($downloadPath !== false)
        {
            $extractPath = storage_path("app\\extract\\{$this->release_id}");
            Log::info("Extracting driver pack {$this->release_id}");

            if(zipExtract($downloadPath, $extractPath))
            {
                return $extractPath;
            }
        }

        return false;
    }
}
