<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'Super Admin'
        ]);

        DB::table('roles')->updateOrInsert([
            'id' => 2
        ], [
            'name' => 'Admin'
        ]);

        DB::table('roles')->updateOrInsert([
            'id' => 3
        ], [
            'name' => 'Convention Member'
        ]);
    }
}
