<?php
use Illuminate\Database\Seeder;
use App\Enum\ConfigTypeEnum;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('config_types')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'Ideapay Fee (Fixed)',
        ]);

        DB::table('configs')->updateOrInsert([
            'id' => 1
        ], [
            'type' => ConfigTypeEnum::IDEAPAY_FEE_FIXED,
            'name' => 'Fixed',
            'value' => '87.5',
        ]);

        DB::table('config_types')->updateOrInsert([
            'id' => 2
        ], [
            'name' => 'Ideapay Fee (Percentage)',
        ]);

        DB::table('configs')->updateOrInsert([
            'id' => 2
        ], [
            'type' => ConfigTypeEnum::IDEAPAY_FEE_PERCENTAGE,
            'name' => 'Percentage',
            'value' => '0.05',
        ]);

        DB::table('config_types')->updateOrInsert([
            'id' => 3
        ], [
            'name' => 'PHP Rate for 1 USD',
        ]);

        DB::table('configs')->updateOrInsert([
            'id' => 3
        ], [
            'type' => ConfigTypeEnum::PHP_RATE_FOR_USD,
            'name' => 'Fixed PHP Rate for 1 USD',
            'value' => '53.00',
        ]);

        DB::table('config_types')->updateOrInsert([
            'id' => 4
        ], [
            'name' => 'Registration',
        ]);

        DB::table('configs')->updateOrInsert([
            'id' => 4
        ], [
            'type' => ConfigTypeEnum::REGISTRATION_SWITCH,
            'name' => 'Enabled',
            'value' => 'Yes',
        ]);
    }
}
