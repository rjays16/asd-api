<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

use App\Models\ForExRate;
use App\Models\Config;

use Carbon\Carbon;

class ForExRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $current_date = Carbon::today()->toDateString();
        $rate_for_today = ForExRate::where('date', $current_date)->first();

        $usd_list = json_decode(file_get_contents(config('settings.FOREX_USD_URL')));
        $php_rate = $usd_list->rates->PHP;

        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/forex/seeder.log'),
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
            } catch(Exception $e){
                DB::rollBack();
                Log::critical(['slack', $channel])->info("Could not create the ForEx rate for today: $current_date \n");
                throw $e;
            }
        } else {
            Log::stack(['slack', $channel])->info("The existing ForEx rate value for today is: $rate_for_today->value \n");
        }
    }
}
