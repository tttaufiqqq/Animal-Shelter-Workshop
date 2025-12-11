<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Roles are stored in Taufiq's database (User Management Module)
     */
    public function run()
    {
        // Use transaction for Taufiq's database
        DB::connection('taufiq')->beginTransaction();

        try {
            // Create roles in Taufiq's database
            // Spatie's Role model already uses the 'taufiq' connection from your config

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

            DB::connection('taufiq')->commit();

            $this->command->info('');
            $this->command->info('Roles seeded successfully in Taufiq\'s database!');

        } catch (\Exception $e) {
            DB::connection('taufiq')->rollBack();

            $this->command->error('Error seeding roles: ' . $e->getMessage());
            throw $e;
        }
    }
}
