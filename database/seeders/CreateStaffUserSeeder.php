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
        // Create roles if they do not exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $caretakerRole = Role::firstOrCreate(['name' => 'caretaker']);

        // Create Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'phoneNum' => '0137121612',
            'address' => '29, Jalan Sejahtera 9',
            'city' => 'Ayer Keroh',
            'state' => 'Melaka',
        ]);

        $admin->assignRole($adminRole);


        // Create Caretaker user
        $caretaker = User::create([
            'name' => 'Caretaker User',
            'email' => 'caretaker@gmail.com',
            'password' => bcrypt('password'),
            'phoneNum' => '0123456789',
            'address' => '8, Jalan Sejahtera 7, Taman Bukit Tambun Perdana',
            'city' => 'Durian Tunggal',
            'state' => 'Melaka',
        ]);

        $caretaker->assignRole($caretakerRole);

    }
}