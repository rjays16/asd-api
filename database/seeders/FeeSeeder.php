<?php

use Illuminate\Database\Seeder;
use App\Enum\FeeTypeEnum;
use App\Enum\WorkshopEnum;
use App\Enum\RegistrationTypeEnum;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fees')->updateOrInsert([
            'id' => 1
        ], [
            'name' => 'Registration A',
            'description' => 'International ASD Member',
            'year' => 2022,
            'is_pds' => false,
            'scope' => true, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 100,
            'late_amount' => 150, # From August 16 onwards
            'member_type' => 1,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 2
        ], [
            'name' => 'Registration B',
            'description' => 'International Non-ASD Member',
            'year' => 2022,
            'is_pds' => false,
            'scope' => true, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 200,
            'late_amount' => 250, # From August 16 onwards
            'member_type' => 2,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 3
        ], [
            'name' => 'Registration C',
            'description' => 'International ASD Resident/Fellow',
            'year' => 2022,
            'is_pds' => false,
            'scope' => true, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 50,
            'late_amount' => 100, # From August 16 onwards
            'member_type' => 3,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 4
        ], [
            'name' => 'Registration D',
            'description' => 'Local ASD Member',
            'year' => 2022,
            'is_pds' => false,
            'scope' => false, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 100,
            'late_amount' => 150, # From August 16 onwards
            'member_type' => 1,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 5
        ], [
            'name' => 'Registration E',
            'description' => 'Local ASD Member (PDS Member)',
            'year' => 2022,
            'is_pds' => true,
            'scope' => false, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 50,
            'late_amount' => 100, # From August 16 onwards
            'member_type' => 1,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 6
        ], [
            'name' => 'Registration F',
            'description' => 'Local Non-ASD Member',
            'year' => 2022,
            'is_pds' => false,
            'scope' => false, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 200,
            'late_amount' => 250, # From August 16 onwards
            'member_type' => 2,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 7
        ], [
            'name' => 'Registration G',
            'description' => 'Local Non-ASD Member (PDS Member)',
            'year' => 2022,
            'is_pds' => true,
            'scope' => false, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 150,
            'late_amount' => 200, # From August 16 onwards
            'member_type' => 2,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);

        DB::table('fees')->updateOrInsert([
            'id' => 8
        ], [
            'name' => 'Registration H',
            'description' => 'Local ASD Resident/Fellow',
            'year' => 2022,
            'is_pds' => false,
            'scope' => false, # if true, it is global (USD). Else, it is local (PHP)
            'amount' => 50,
            'late_amount' => 100, # From August 16 onwards
            'member_type' => 3,
            'late_amount_starts_on' => '2022-08-16',
            'uses_late_amount' => true,
        ]);
    }
}