<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\ForExRate;
use App\Models\Config;

use Exception;
use DB;

use Carbon\Carbon;

class ForExRateController extends Controller
{
    public function getActivePHPRate() {
        $php_rate = ForExRate::active()->first();

        if(!is_null($php_rate)) {
            return response()->json([
                'php_rate' => $php_rate->value
            ]);
        } else {
            return response()->json([
                'message' => 'The ForEx rate for PHP-USD has not been set yet.'
            ], 404);
        }
    }

    public function create() {
        $current_date = Carbon::today()->toDateString();
        $rate_for_today = ForExRate::where('date', $current_date)->first();

        $usd_list = json_decode(file_get_contents(config('settings.FOREX_USD_URL')));
        $php_rate = $usd_list->rates->PHP;

        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/forex/controller.log'),
        ]);
        
        if(is_null($rate_for_today)) {
            DB::beginTransaction();
            try {
                ForExRate::query()
                    ->where('date', '!=', $current_date)
                    ->update(['is_active' => false]);

                $forex_rate = new ForExRate();
                $forex_rate->value = $php_rate;
                $forex_rate->is_active = true;
                $forex_rate->date = Carbon::today()->toDateString();
                $forex_rate->save();

                DB::commit();
                Log::stack(['slack', $channel])->info("The new ForEx rate value for today is: $forex_rate->value \n");
                return response()->json([
                    'message' => 'Successfully created the ForEx rate for PHP-USD.',
                    'php_usd' => $forex_rate->value
                ]);
            } catch(Exception $e){
                DB::rollBack();
                Log::critical(['slack', $channel])->info("Could not create the ForEx rate for today: $current_date \n");
                throw $e;
            }
        } else {
            Log::stack(['slack', $channel])->info("The existing ForEx rate value for today is: $rate_for_today->value \n");
            return response()->json([
                'php_rate' => $php_rate,
                'message' => 'There is already an existing ForEx rate for PHP-USD.'
            ]);
        }
    }
}