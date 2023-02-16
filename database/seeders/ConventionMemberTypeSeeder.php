<?php

use Illuminate\Database\Seeder;

class ConventionMemberTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('convention_member_types')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'ASD Member',
        ]);

        DB::table('convention_member_types')->updateOrInsert([
            'id' => 2
        ], [
            'name' => 'Non-ASD',
        ]);

        DB::table('convention_member_types')->updateOrInsert([
            'id' => 3
        ], [
            'name' => 'Resident/Fellow',
        ]);

        DB::table('convention_member_types')->updateOrInsert([
            'id' => 4
        ], [
            'name' => 'Speaker',
        ]);
    }
}