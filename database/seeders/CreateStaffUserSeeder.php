<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateStaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);

        $user = User::create([
            'name' => 'Danish Admin',
            'email' => 'danishIrwan@staff.com',
            'password' => bcrypt('password'),
            'phoneNum' => '0137121612',
            'address' => '29, Jalan Sejahtera 9',
            'city' => 'Ayer Keroh',
            'state' => 'Melaka',
            
        ]);

        $user->assignRole($role);
    }
}