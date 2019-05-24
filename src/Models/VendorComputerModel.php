<?php

namespace Klepak\DriverManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Klepak\DriverManagement\Models\HP\HpComputerModel;
use Exception;
use Klepak\DriverManagement\Models\Dell\DellComputerModel;
use Klepak\DriverManagement\Models\Lenovo\LenovoComputerModel;

abstract class VendorComputerModel extends Model
{
    const HP = "HP";
    const DELL = "Dell";
    const LENOVO = "Lenovo";

    protected $guarded = [];
    public $timestamps = false;

    protected $primaryKey = "id";
    public $incrementing = false;

    public static function getSupportedVendors()
    {
        return [self::HP, self::DELL, self::LENOVO];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCertificationInfo()
    {
        return [
            "name" => $this->name
        ];
    }

    public static function fromVendorIdentifier($vendorIdentifier, $selector = false)
    {
        switch($vendorIdentifier)
        {
            case static::HP:
                $model = new HpComputerModel;
                break;
            case static::DELL:
                $model = new DellComputerModel;
                break;
            case static::LENOVO:
                $model = new LenovoComputerModel;
                break;

            default:
                throw new Exception("Vendor identifier $vendorIdentifier unknown - supported vendors: " . implode(",", static::getSupportedVendors()));
        }

        if($selector !== false)
        {
            // build query
            foreach($selector as $key => $value)
            {
                // TODO: disallow non-alphanumeric
                $value = str_replace("*", "%", $value);

                if($vendorIdentifier == static::HP && $key == "system_id")
                {
                    $value = "%$value%";
                }

                $model = $model->where($key, "LIKE", $value);
            }
        }

        return $model;
    }
}
