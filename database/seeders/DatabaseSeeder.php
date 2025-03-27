<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // php artisan db:seed --class=AssignAdminRoleSeeder
    // php artisan tinker
    // \App\Models\User::find(2)->getRoleNames();
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AssignAdminRoleSeeder::class,
        ]);
    }
}

