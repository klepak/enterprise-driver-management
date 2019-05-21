<?php

namespace Klepak\DriverManagement\Models\HP;

use Illuminate\Database\Eloquent\Model;

class HpDriverPack extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $primaryKey = "id";
    public $incrementing = false;
}
