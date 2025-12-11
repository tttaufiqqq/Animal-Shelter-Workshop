<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Users and Roles are both in Taufiq's database
     */
    public function run(): void
    {
        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            $this->command->info('Seeding users in Taufiq\'s database...');
            $this->command->info('========================================');

            // Create roles if they don't exist (all in Taufiq's database)
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $caretakerRole = Role::firstOrCreate(['name' => 'caretaker']);
            $publicRole = Role::firstOrCreate(['name' => 'public user']);

            $this->command->info('✓ Roles verified');

            // --- Admins ---
            $this->command->info('');
            $this->command->info('Creating Admins...');

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

                // Assign role if not already assigned
                if (!$admin->hasRole($adminRole)) {
                    $admin->assignRole($adminRole);
                }

                $this->command->info("  ✓ {$admin->name} ({$admin->email})");
            }

            // --- Caretakers ---
            $this->command->info('');
            $this->command->info('Creating Caretakers...');

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

                // Assign role if not already assigned
                if (!$caretaker->hasRole($caretakerRole)) {
                    $caretaker->assignRole($caretakerRole);
                }

                $this->command->info("  ✓ {$caretaker->name} ({$caretaker->email})");
            }

            // --- Public Users (Your Team) ---
            $this->command->info('');
            $this->command->info('Creating Public Users...');

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
                        'city' => 'Ayer Keroh',
                        'state' => 'Melaka',
                    ])
                );

                // Assign role if not already assigned
                if (!$user->hasRole($publicRole)) {
                    $user->assignRole($publicRole);
                }

                $this->command->info("  ✓ {$user->name} ({$user->email})");
            }

            DB::connection('taufiq')->commit();

            $this->command->info('');
            $this->command->info('========================================');
            $this->command->info('✓ All users seeded successfully in Taufiq\'s database!');
            $this->command->info('');
            $this->command->info('Login Credentials:');
            $this->command->info('  Email: any of the above emails');
            $this->command->info('  Password: password');

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding users: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
