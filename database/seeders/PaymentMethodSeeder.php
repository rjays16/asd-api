<?php

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_methods')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'Ideapay'
        ]);
    }
}