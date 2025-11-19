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
            RoleSeeder::class,
            UserSeeder::class,
            ReportSeeder::class,
            RescueSeeder::class,
            CategorySeeder::class,
            AnimalSeeder::class,
            BookingSeeder::class,
            TransactionSeeder::class,
            AdoptionSeeder::class,
        ]);
    }
}