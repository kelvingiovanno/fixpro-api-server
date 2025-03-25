<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\UsersRole;
use App\Models\User;
use App\Models\UserStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UsersRole::create(['role' => 'member']);
        UsersRole::create(['role' => 'maintenance']);
        UsersRole::create(['role' => 'management']);

        UserStatus::create(['status' => 'pending']);
        UserStatus::create(['status' => 'accepted']);
        UserStatus::create(['status' => 'rejected']);

        User::create(['role_id' => 1, 'instalation_id' => '123kwjqjkdhsajkhdkajshd', 'status_id' => 1]);
        User::create(['role_id' => 1, 'instalation_id' => '512398u89qwdshakjsalkds', 'status_id' => 1]);
    }
}
