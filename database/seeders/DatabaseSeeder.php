<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ... (User factory comments remain optional)

        $this->call([
            // 1. RoleSeeder must be called first
            RoleSeeder::class,
            
            // 2. CreateStaffUserSeeder will be called second, 
            //    and can now rely on roles being present
            CreateStaffUserSeeder::class,
            CaretakerSeeder::class,
            CategorySeeder::class,
            AnimalSeeder::class,
            BookingSeeder::class,
            TransactionSeeder::class,
            AdoptionSeeder::class,
        ]);
    }
}