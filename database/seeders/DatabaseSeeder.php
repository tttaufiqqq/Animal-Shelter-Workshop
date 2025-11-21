<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
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
            AnimalProfileSeeder::class,
        ]);
    }
}