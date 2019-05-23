<?php

namespace Klepak\DriverManagement\Models\Dell;

use Illuminate\Database\Eloquent\Model;

class DellHardwareDevice extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected $primaryKey = "component_id";
    public $incrementing = false;

    protected $casts = [
        "pci_info" => "array",
        "pnp_info" => "array",
        "embedded" => "boolean"
    ];

    public function softwareComponents() {
        return \Klepak\DriverManagement\Models\Dell\DellSoftwareComponent::where("supported_devices", "like", "%{$this->component_id}%")->get();
    }
}
