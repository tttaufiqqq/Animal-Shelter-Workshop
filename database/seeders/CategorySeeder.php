<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Categories are stored in Atiqah's database (Shelter Management Module)
     */
    public function run()
    {
        $this->command->info('Starting Category Seeder...');
        $this->command->info('========================================');

        $categories = [
            // Food
            ['main' => 'Food', 'sub' => 'Dog Food'],
            ['main' => 'Food', 'sub' => 'Cat Food'],
            ['main' => 'Food', 'sub' => 'Puppy & Kitten Formula'],

            // Supplements
            ['main' => 'Supplements', 'sub' => 'Vitamins & Minerals'],
            ['main' => 'Supplements', 'sub' => 'Probiotics & Digestive Aid'],
            ['main' => 'Supplements', 'sub' => 'Joint & Mobility Support'],

            // Medicine + Health
            ['main' => 'Medicine', 'sub' => 'Deworming Medication'],
            ['main' => 'Medicine', 'sub' => 'Flea & Tick Treatment'],
            ['main' => 'Medicine', 'sub' => 'Vaccines & Antibiotics'],

            // Cleaning Supplies
            ['main' => 'Cleaning', 'sub' => 'Disinfectant'],
            ['main' => 'Cleaning', 'sub' => 'Detergent & Soap'],
            ['main' => 'Cleaning', 'sub' => 'Waste Disposal Supplies'],

            // Equipment
            ['main' => 'Equipment', 'sub' => 'Leashes & Collars'],
            ['main' => 'Equipment', 'sub' => 'Food & Water Bowls'],
            ['main' => 'Equipment', 'sub' => 'Cages & Carriers'],

            // Grooming
            ['main' => 'Grooming', 'sub' => 'Shampoo & Conditioner'],
            ['main' => 'Grooming', 'sub' => 'Brushes & Combs'],
            ['main' => 'Grooming', 'sub' => 'Nail Clippers'],
        ];

        // Add timestamps to each category
        $now = now();
        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        // Use transaction for Atiqah's database
        DB::connection('shelter')->beginTransaction();

        try {
            $this->command->info('Inserting categories into Atiqah\'s database...');

            // Insert categories into Atiqah's database
            DB::connection('shelter')->table('category')->insert($categories);

            DB::connection('shelter')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Category Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info('Total categories created: ' . count($categories));
            $this->command->info('Database: Atiqah (MySQL)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('shelter')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding categories: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
