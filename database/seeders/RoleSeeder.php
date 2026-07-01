<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Roles are stored in Taufiq - Users Management
     */
    public function run()
    {
        // Use transaction for Taufiq - Users Management database
        DB::connection('users')->beginTransaction();

        try {
            // Create roles in Taufiq - Users Management database
            // Spatie's Role model already uses the 'users' connection from your config

            $roles = [
                'public user',
                'admin',
                'caretaker',
                'adopter'
            ];

            foreach ($roles as $roleName) {
                // Check if role already exists to avoid duplicates
                if (!Role::where('name', $roleName)->exists()) {
                    Role::create(['name' => $roleName]);
                    $this->command->info("✓ Created role: {$roleName}");
                } else {
                    $this->command->warn("⚠ Role already exists: {$roleName}");
                }
            }

            DB::connection('users')->commit();

            $this->command->info('');
            $this->command->info('Roles seeded successfully in Taufiq - Users Management database!');

        } catch (\Exception $e) {
            DB::connection('users')->rollBack();

            $this->command->error('Error seeding roles: ' . $e->getMessage());
            throw $e;
        }
    }
}
