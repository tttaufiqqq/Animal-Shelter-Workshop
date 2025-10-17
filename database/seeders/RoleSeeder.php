<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create the 'public user' role
        Role::create(['name' => 'public user']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'adopter']);
    }
}
