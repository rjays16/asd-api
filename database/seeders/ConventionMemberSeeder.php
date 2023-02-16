<?php

use Illuminate\Database\Seeder;

class ConventionMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('convention_members')->updateOrInsert([
            'user_id' => 7
        ], [
            'member_type' => 1,
            'scope' => 1,
            'is_pds' => false,
            'pds_number' => null,
            'prc_number' => null,
            'resident_certificate' => null,
            'institution_organization' => null
        ]);
    }
}