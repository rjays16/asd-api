<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Config;

use App\Enum\ConfigTypeEnum;
use App\Http\Requests\Registration\UpdateSettings;

use Exception;
use DB;

class ConfigController extends Controller
{
    public function getIdeapayFee() {
        $delivery_fee = Config::where('type', ConfigTypeEnum::IDEAPAY_FEE_FIXED)
            ->where('name', 'Fixed')
            ->first();

        if(!is_null($delivery_fee)) {
            return response()->json($delivery_fee);
        } else {
            return response()->json(['message' => 'Fee has not been set yet'], 404);
        }
    }

    public function updateIdeapayFee(Update $request) {
        $validated = $request->validated();

        $delivery_fee = Config::where('type', ConfigTypeEnum::IDEAPAY_FEE_FIXED)
            ->where('name', 'Fixed')
            ->first();

        DB::beginTransaction();
        try {
            if(is_null($delivery_fee)) {
                $delivery_fee = new Config();
                $delivery_fee->type = ConfigTypeEnum::IDEAPAY_FEE_FIXED;
                $delivery_fee->name = 'Fixed';
            }

            $delivery_fee->value = $validated["value"];
            $delivery_fee->save();   

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated fee'
            ]);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getIdeapayRate() {
        return response()->json([
            'rate' => Config::getIdeapayRate()
        ]);
    }

    public function getPHPRateForUSD() {
        return response()->json([
            'php_rate' => Config::getPHPRateForUSD()
        ]);
    }

    public function getRegistrationSwitch() {
        $switch = Config::where('type', ConfigTypeEnum::REGISTRATION_SWITCH)->first();

        if(!is_null($switch)) {
            $value = $switch->value == "Yes" ? true : false;
            return response()->json($value);
        } else {
            return response()->json(['message' => 'Registration setting has not been set yet'], 404);
        }
    }

    public function updateRegistrationSwitch(UpdateSettings $request) {
        $validated = $request->validated();

        $switch = Config::where('type', ConfigTypeEnum::REGISTRATION_SWITCH)->first();

        DB::beginTransaction();
        try {
            if(is_null($switch)) {
                $switch = new Config();
                $switch->type = ConfigTypeEnum::REGISTRATION_SWITCH;
                $switch->name = 'Enabled';
            }

            $switch->value = $validated["value"] == true ? "Yes" : "No";
            $switch->save();   

            DB::commit();
            return response()->json([
                'message' => 'Successfully updated registration setting',
                'status' => $switch->value == "Yes" ? "Enabled" : "Disabled"
            ]);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
