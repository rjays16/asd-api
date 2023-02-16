<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Config;

class ForExRate extends Model
{
    use SoftDeletes;

    public $table = 'forex_rates';

    protected $fillable = [
        'value',
        'is_active',
        'date',
    ];

    protected $casts = [
		'date' => 'date:Y-m-d',
    ];

    public function scopeActive($query) {
		return $query->where('is_active', true);
	}

    public static function getActivePHPRate() {
        $php_rate = self::active()->first();

        if(!is_null($php_rate)) {
            return $php_rate->value;
        } else {
            return Config::getPHPRateForUSD();
        }
    }

    public static function convertAmountToPHP($amount) {
        return self::getActivePHPRate() * $amount;
    }
}