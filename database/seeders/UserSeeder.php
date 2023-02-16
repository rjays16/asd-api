<?php

use Illuminate\Database\Seeder;
use App\Enum\RoleEnum;
use App\Enum\UserStatusEnum;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->updateOrInsert([
            'id' => 1
        ], [
            'first_name' => 'Admin',
            'email' => 'registration@asdmeeting2022.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::ADMIN,
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 2
        ], [
            'first_name' => 'Admin',
            'last_name' => 'Dave',
            'email' => 'dave.c@rightteamprovider.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::SUPER_ADMIN
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 3
        ], [
            'first_name' => 'Admin',
            'last_name' => 'John',
            'email' => 'john.r@rightteamprovider.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::SUPER_ADMIN
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 4
        ], [
            'first_name' => 'Admin',
            'email' => 'marwil.b@rightteamprovider.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::SUPER_ADMIN
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 5
        ], [
            'first_name' => 'Admin',
            'email' => 'rian.b@rightteamprovider.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::SUPER_ADMIN
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 6
        ], [
            'first_name' => 'Admin',
            'email' => 'sam@test.com',
            'password' => app('hash')->make(config('settings.DEFAULT_ADMIN_PASSWORD')),
            'role' => RoleEnum::SUPER_ADMIN
        ]);

        DB::table('users')->updateOrInsert([
            'id' => 7
        ], [
            'first_name' => 'International',
            'last_name' => 'Asd',
            'email' => 'asd@gmail.com',
            'password' => app('hash')->make(config('settings.DEFAULT_MEMBER_PASSWORD')),
            'country' => 'Philippines',
            'role' => RoleEnum::CONVENTION_MEMBER,
            'status' => 1
        ]);
    }
}