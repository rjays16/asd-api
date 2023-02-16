<?php

use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('order_status')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'Pending'
        ]);

        DB::table('order_status')->updateOrInsert([
            'id' => 2
        ], [
            'name' => 'Completed'
        ]);

        DB::table('order_status')->updateOrInsert([
            'id' => 3
        ], [
            'name' => 'Failed'
        ]);
    }
}