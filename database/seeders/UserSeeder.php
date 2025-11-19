<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $caretakerRole = Role::firstOrCreate(['name' => 'caretaker']);
        $publicRole = Role::firstOrCreate(['name' => 'public user']);

        // --- Admins ---
        $admins = [
            [
                'name' => 'Admin 1',
                'email' => 'admin1@gmail.com',
                'phoneNum' => '0137000001',
                'address' => 'Address Admin 1',
                'city' => 'City Admin',
                'state' => 'Melaka',
            ],
            [
                'name' => 'Admin 2',
                'email' => 'admin2@gmail.com',
                'phoneNum' => '0137000002',
                'address' => 'Address Admin 2',
                'city' => 'City Admin',
                'state' => 'Melaka',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = User::firstOrCreate(
                ['email' => $adminData['email']],
                array_merge($adminData, ['password' => bcrypt('password')])
            );
            $admin->assignRole($adminRole);
        }

        // --- Caretakers ---
        $caretakers = [
            [
                'name' => 'Caretaker 1',
                'email' => 'caretaker1@gmail.com',
                'phoneNum' => '0123000001',
                'address' => 'Address Caretaker 1',
                'city' => 'City Caretaker',
                'state' => 'Melaka',
            ],
            [
                'name' => 'Caretaker 2',
                'email' => 'caretaker2@gmail.com',
                'phoneNum' => '0123000002',
                'address' => 'Address Caretaker 2',
                'city' => 'City Caretaker',
                'state' => 'Melaka',
            ],
        ];

        foreach ($caretakers as $caretakerData) {
            $caretaker = User::firstOrCreate(
                ['email' => $caretakerData['email']],
                array_merge($caretakerData, ['password' => bcrypt('password')])
            );
            $caretaker->assignRole($caretakerRole);
        }

        // --- Public Users ---
        $publicUsers = [
            ['name' => 'Taufiq', 'email' => 'taufiq@gmail.com'],
            ['name' => 'Shafiqah', 'email' => 'shafiqah@gmail.com'],
            ['name' => 'Atiqah', 'email' => 'atiqah@gmail.com'],
            ['name' => 'Danish', 'email' => 'danish@gmail.com'],
            ['name' => 'Eilya', 'email' => 'eilya@gmail.com'],
        ];

        foreach ($publicUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => bcrypt('password'),
                    'phoneNum' => '0100000000',
                    'address' => '17, Jalan Sejahtera 1',
                    'city' => 'Ayer Keorh',
                    'state' => 'Melaka',
                ])
            );
            $user->assignRole($publicRole);
        }
    }
}
