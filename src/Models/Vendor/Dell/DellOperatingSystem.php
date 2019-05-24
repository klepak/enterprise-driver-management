<?php

namespace Klepak\DriverManagement\Models\Vendor\Dell;

use Illuminate\Database\Eloquent\Model;

class DellOperatingSystem extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected $primaryKey = "os_code";
    public $incrementing = false;
}
