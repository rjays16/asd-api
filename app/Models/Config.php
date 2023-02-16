<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enum\ConfigTypeEnum;

class Config extends Model
{
    public $table = "configs";

    protected $fillable = [
        'type',
        'name',
        'value'
    ];

    public function config_type() {
        return $this->belongsToMany(ConfigType::class, 'type');
    }

    public static function getIdeapayFee() {
        $ideapay_fee = self::where('type', ConfigTypeEnum::IDEAPAY_FEE_FIXED)
            ->where('name', 'Fixed')
            ->first();

        if(!is_null($ideapay_fee)) {
            return $ideapay_fee->value;
        } else {
            return 0;
        }
    }

    public static function getIdeapayRate() {
        $ideapay_rate = self::where('type', ConfigTypeEnum::IDEAPAY_FEE_PERCENTAGE)
            ->where('name', 'Percentage')
            ->first();

        if(!is_null($ideapay_rate)) {
            return $ideapay_rate->value;
        } else {
            return 0;
        }
    }

    public static function getPHPRateForUSD() {
        $php_rate_for_usd = self::where('type', ConfigTypeEnum::PHP_RATE_FOR_USD)->first();

        if(!is_null($php_rate_for_usd)) {
            return $php_rate_for_usd->value;
        } else {
            return 50.0;
        }
    }
}