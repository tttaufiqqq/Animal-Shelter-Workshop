<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Food
            ['main' => 'Food', 'sub' => 'Dry Dog Food'],
            ['main' => 'Food', 'sub' => 'Wet Dog Food'],
            ['main' => 'Food', 'sub' => 'Dry Cat Food'],
            ['main' => 'Food', 'sub' => 'Wet Cat Food'],
            ['main' => 'Food', 'sub' => 'Puppy Formula'],
            ['main' => 'Food', 'sub' => 'Kitten Formula'],
            ['main' => 'Food', 'sub' => 'Treats'],

            // Medicine + Health
            ['main' => 'Medicine', 'sub' => 'Deworming Medication'],
            ['main' => 'Medicine', 'sub' => 'Flea & Tick Treatment'],
            ['main' => 'Medicine', 'sub' => 'Vaccines'],
            ['main' => 'Medicine', 'sub' => 'Antibiotics'],
            ['main' => 'Medicine', 'sub' => 'Bandages & First Aid'],

            // Cleaning Supplies
            ['main' => 'Cleaning', 'sub' => 'Disinfectant'],
            ['main' => 'Cleaning', 'sub' => 'Detergent'],
            ['main' => 'Cleaning', 'sub' => 'Trash Bags'],
            ['main' => 'Cleaning', 'sub' => 'Gloves'],

            // Equipment
            ['main' => 'Equipment', 'sub' => 'Leashes & Collars'],
            ['main' => 'Equipment', 'sub' => 'Transport Carriers'],
            ['main' => 'Equipment', 'sub' => 'Food Bowls'],
            ['main' => 'Equipment', 'sub' => 'Water Dispensers'],
            ['main' => 'Equipment', 'sub' => 'Cages & Kennels'],

            // Bedding
            ['main' => 'Bedding', 'sub' => 'Pet Beds'],
            ['main' => 'Bedding', 'sub' => 'Blankets'],
            ['main' => 'Bedding', 'sub' => 'Towels'],

            // Grooming
            ['main' => 'Grooming', 'sub' => 'Shampoo'],
            ['main' => 'Grooming', 'sub' => 'Brushes'],
            ['main' => 'Grooming', 'sub' => 'Nail Clippers'],

            // Office
            ['main' => 'Office', 'sub' => 'Paper'],
            ['main' => 'Office', 'sub' => 'Printer Ink'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
