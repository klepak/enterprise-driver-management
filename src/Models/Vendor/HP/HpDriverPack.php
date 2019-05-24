<?php

namespace Klepak\DriverManagement\Models\Vendor\HP;

use Illuminate\Database\Eloquent\Model;

class HpDriverPack extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $primaryKey = "id";
    public $incrementing = false;

    protected $casts = [
        "system_id" => "array",
    ];
}
