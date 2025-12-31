<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sections and Slots are stored in Atiqah's database (Shelter Management Module)
     *
     * REALISTIC CAPACITY DISTRIBUTION:
     * - Adult areas: Mostly capacity 1, some bonded pairs (capacity 2)
     * - Nurseries: Mix of individual and litter pens (capacity 1, 4, 8)
     * - Medical/Isolation: Strict capacity 1 (isolation required)
     *
     * SLOT STATUS AUTO-CALCULATION:
     * - All slots are created with 'available' status (no animals assigned yet)
     * - When AnimalSeeder runs (after this seeder), slot status is recalculated:
     *   → 'occupied' if animalCount >= capacity
     *   → 'available' if animalCount < capacity
     *   → 'maintenance' (manual admin override only)
     * - Status is also recalculated when animals are added, deleted, or adopted
     */
    public function run(): void
    {
        $this->command->info('Starting Section & Slot Seeder (Realistic Capacity)...');
        $this->command->info('========================================');

        // Define sections with capacity distribution
        $sections = [
            [
                'name' => 'Cat Zone',
                'description' => 'Dedicated area for cats with climbing structures and cozy spaces',
                'slots' => [
                    ['capacity' => 1, 'count' => 24], // 80% individual cats
                    ['capacity' => 2, 'count' => 6],  // 20% bonded pairs
                ],
            ],
            [
                'name' => 'Dog Area',
                'description' => 'Spacious area for dogs with play equipment and exercise space',
                'slots' => [
                    ['capacity' => 1, 'count' => 36], // 90% individual dogs
                    ['capacity' => 2, 'count' => 4],  // 10% bonded pairs
                ],
            ],
            [
                'name' => 'Puppy Nursery',
                'description' => 'Special care area for puppies and young dogs',
                'slots' => [
                    ['capacity' => 1, 'count' => 5],  // Individual puppies
                    ['capacity' => 4, 'count' => 7],  // Litter pens (same-litter puppies)
                    ['capacity' => 8, 'count' => 3],  // Mother + litter pens
                ],
            ],
            [
                'name' => 'Kitten Corner',
                'description' => 'Warm and safe environment for kittens',
                'slots' => [
                    ['capacity' => 1, 'count' => 8],  // Individual kittens
                    ['capacity' => 5, 'count' => 10], // Litter cages (same-litter kittens)
                    ['capacity' => 8, 'count' => 2],  // Mother + litter cages
                ],
            ],
            [
                'name' => 'Medical Ward',
                'description' => 'Quarantine and recovery area for animals receiving treatment',
                'slots' => [
                    ['capacity' => 1, 'count' => 10], // Strict isolation (100%)
                ],
            ],
            [
                'name' => 'Isolation Unit',
                'description' => 'Separate area for animals with contagious conditions',
                'slots' => [
                    ['capacity' => 1, 'count' => 8],  // Strict isolation (100%)
                ],
            ],
        ];

        $totalSlots = 0;
        $capacityBreakdown = [];

        // Use transaction for Atiqah's database
        DB::connection('atiqah')->beginTransaction();

        try {
            // Insert sections and create slots for each
            foreach ($sections as $sectionData) {
                // Insert section into Atiqah's database
                $sectionId = DB::connection('atiqah')->table('section')->insertGetId([
                    'name' => $sectionData['name'],
                    'description' => $sectionData['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create slots for this section with different capacities
                $allSlots = [];
                $currentLetter = 'A';
                $currentNumber = 1;
                $slotsPerRow = 10; // 10 slots per row before moving to next letter
                $sectionSlotCount = 0;

                foreach ($sectionData['slots'] as $slotGroup) {
                    $capacity = $slotGroup['capacity'];
                    $count = $slotGroup['count'];

                    for ($i = 0; $i < $count; $i++) {
                        $slotName = $currentLetter . $currentNumber;

                        // Add capacity suffix for non-standard capacities (for clarity)
                        if ($capacity > 1) {
                            $slotName .= " (×{$capacity})";
                        }

                        $allSlots[] = [
                            'name' => $slotName,
                            'sectionID' => $sectionId,
                            'capacity' => $capacity,
                            'status' => 'available', // Initial status (no animals assigned yet)
                            // NOTE: Status will be auto-calculated when animals are assigned:
                            //   - 'occupied' if animalCount >= capacity
                            //   - 'available' if animalCount < capacity
                            //   - 'maintenance' (manual override only)
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $totalSlots++;
                        $sectionSlotCount++;

                        // Track capacity distribution
                        $key = "capacity_{$capacity}";
                        if (!isset($capacityBreakdown[$key])) {
                            $capacityBreakdown[$key] = 0;
                        }
                        $capacityBreakdown[$key]++;

                        // Move to next slot number
                        $currentNumber++;

                        // If we've reached 10 slots, move to next letter
                        if ($currentNumber > $slotsPerRow) {
                            $currentNumber = 1;
                            $currentLetter++;
                        }
                    }
                }

                // Insert slots for this section in chunks into Atiqah's database
                foreach (array_chunk($allSlots, 50) as $chunk) {
                    DB::connection('atiqah')->table('slot')->insert($chunk);
                }

                $this->command->info("✓ Created section '{$sectionData['name']}' with {$sectionSlotCount} slots");

                // Show capacity breakdown for this section
                foreach ($sectionData['slots'] as $slotGroup) {
                    $capacity = $slotGroup['capacity'];
                    $count = $slotGroup['count'];
                    $percentage = round(($count / $sectionSlotCount) * 100);
                    $this->command->info("  - Capacity {$capacity}: {$count} slots ({$percentage}%)");
                }
            }

            DB::connection('atiqah')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Section & Slot Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info('Total Sections: ' . count($sections));
            $this->command->info('Total Slots: ' . $totalSlots);
            $this->command->info('');
            $this->command->info('Capacity Distribution (Industry Standard):');
            ksort($capacityBreakdown);
            foreach ($capacityBreakdown as $key => $count) {
                $capacity = str_replace('capacity_', '', $key);
                $percentage = round(($count / $totalSlots) * 100, 1);
                $this->command->info("  - Capacity {$capacity}: {$count} slots ({$percentage}%)");
            }
            $this->command->info('');
            $this->command->info('Realistic Features:');
            $this->command->info('  ✓ Individual housing (capacity 1) for adults');
            $this->command->info('  ✓ Bonded pair cages (capacity 2) for compatible animals');
            $this->command->info('  ✓ Litter pens (capacity 4-5) for young animals');
            $this->command->info('  ✓ Mother + litter pens (capacity 8) for nursing');
            $this->command->info('  ✓ Strict isolation (capacity 1) for medical cases');
            $this->command->info('');
            $this->command->info('Database: Atiqah (MySQL)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('atiqah')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding sections and slots: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
