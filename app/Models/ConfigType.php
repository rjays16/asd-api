<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigType extends Model
{
    public $table = "config_types";

    protected $fillable = [
        'name'
    ];
}