<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call('RoleSeeder');
         $this->call('UserStatusSeeder');
         $this->call('UserSeeder');
         $this->call('ConventionMemberTypeSeeder');
         $this->call('ConventionMemberSeeder');
         $this->call('ConfigSeeder');
         $this->call('FeeSeeder');
         $this->call('OrderStatusSeeder');
         $this->call('IdeapayStatusSeeder');
         $this->call('PaymentMethodSeeder');
         $this->call('CountrySeeder');
         $this->call('ForExRateSeeder');
    }
}
