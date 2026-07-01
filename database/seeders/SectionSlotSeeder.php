<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Concerns\SectionSlotSeeder\ProvidesSectionData;

class SectionSlotSeeder extends Seeder
{
    use ProvidesSectionData;

    public function run(): void
    {
        $this->command->info('Starting Section & Slot Seeder (Realistic Capacity)...');
        $this->command->info('========================================');

        $sections         = $this->getSections();
        $totalSlots       = 0;
        $capacityBreakdown = [];

        DB::connection('shelter')->beginTransaction();
        try {
            foreach ($sections as $sectionData) {
                $sectionId = DB::connection('shelter')->table('section')->insertGetId([
                    'name'        => $sectionData['name'],
                    'description' => $sectionData['description'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $allSlots        = [];
                $currentLetter   = 'A';
                $currentNumber   = 1;
                $slotsPerRow     = 10;
                $sectionSlotCount = 0;

                foreach ($sectionData['slots'] as $slotGroup) {
                    $capacity = $slotGroup['capacity'];
                    $count    = $slotGroup['count'];

                    for ($i = 0; $i < $count; $i++) {
                        $slotName = $currentLetter . $currentNumber;
                        if ($capacity > 1) {
                            $slotName .= " (×{$capacity})";
                        }

                        $allSlots[] = [
                            'name'      => $slotName,
                            'sectionID' => $sectionId,
                            'capacity'  => $capacity,
                            'status'    => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $totalSlots++;
                        $sectionSlotCount++;

                        $key = "capacity_{$capacity}";
                        $capacityBreakdown[$key] = ($capacityBreakdown[$key] ?? 0) + 1;

                        $currentNumber++;
                        if ($currentNumber > $slotsPerRow) {
                            $currentNumber = 1;
                            $currentLetter++;
                        }
                    }
                }

                foreach (array_chunk($allSlots, 50) as $chunk) {
                    DB::connection('shelter')->table('slot')->insert($chunk);
                }

                $this->command->info("✓ Created section '{$sectionData['name']}' with {$sectionSlotCount} slots");
                foreach ($sectionData['slots'] as $slotGroup) {
                    $pct = round(($slotGroup['count'] / $sectionSlotCount) * 100);
                    $this->command->info("  - Capacity {$slotGroup['capacity']}: {$slotGroup['count']} slots ({$pct}%)");
                }
            }

            DB::connection('shelter')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Section & Slot Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info('Total Sections: ' . count($sections));
            $this->command->info('Total Slots: ' . $totalSlots);
            $this->command->info('');
            $this->command->info('Capacity Distribution:');
            ksort($capacityBreakdown);
            foreach ($capacityBreakdown as $key => $count) {
                $capacity   = str_replace('capacity_', '', $key);
                $percentage = round(($count / $totalSlots) * 100, 1);
                $this->command->info("  - Capacity {$capacity}: {$count} slots ({$percentage}%)");
            }
            $this->command->info('Database: Atiqah - Shelter Management (MySQL)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('shelter')->rollBack();
            $this->command->error('Error seeding sections and slots: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');
            throw $e;
        }
    }
}
