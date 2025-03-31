<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\UserRole;
use App\Models\User;

use App\Enums\UserRoleEnum;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserRole::create(['id' => UserRoleEnum::MEMBER, 'role' => UserRoleEnum::MEMBER->label()]);
        UserRole::create(['id' => UserRoleEnum::CREW, 'role' => UserRoleEnum::CREW->label()]);
        UserRole::create(['id' => UserRoleEnum::MANAGEMENT,'role' => UserRoleEnum::MANAGEMENT->label()]);

        User::create(['role_id' => 1, 'application_id' => '123kwjqjkdhsajkhdkajshd']);
        User::create(['role_id' => 1, 'application_id' => '512398u89qwdshakjsalkds']);
    }
}
