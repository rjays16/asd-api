<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\ForExRate;

use Exception;
use DB;

use Carbon\Carbon;

class CreateDailyForExRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dailyForExRate:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create daily ForEx rates from the exchange rates API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $current_date = Carbon::today()->toDateString();
        $rate_for_today = ForExRate::where('date', $current_date)->first();

        $usd_list = json_decode(file_get_contents(config('settings.FOREX_USD_URL')));
        $php_rate = $usd_list->rates->PHP;
        
        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/forex/cron.log'),
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