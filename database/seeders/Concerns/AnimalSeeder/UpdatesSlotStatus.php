<?php

namespace Database\Seeders\Concerns\AnimalSeeder;

use Illuminate\Support\Facades\DB;

trait UpdatesSlotStatus
{
    private function updateSlotStatuses(array $animals): array
    {
        try {
            $assignedSlotIds = array_filter(array_column($animals, 'slotID'));

            if (empty($assignedSlotIds)) {
                return [];
            }

            $slotCounts    = array_count_values($assignedSlotIds);
            $slots         = DB::connection('shelter')
                ->table('slot')
                ->whereIn('id', array_keys($slotCounts))
                ->get(['id', 'capacity', 'name']);

            $occupiedCount  = 0;
            $availableCount = 0;

            foreach ($slots as $slot) {
                $animalCount = $slotCounts[$slot->id] ?? 0;
                $newStatus   = $animalCount >= $slot->capacity ? 'occupied' : 'available';

                if ($newStatus === 'occupied') {
                    $occupiedCount++;
                } else {
                    $availableCount++;
                }

                DB::connection('shelter')->table('slot')->where('id', $slot->id)->update([
                    'status'     => $newStatus,
                    'updated_at' => now(),
                ]);

                $this->command->info("  Slot {$slot->name}: {$animalCount}/{$slot->capacity} animals → status: {$newStatus}");
            }

            $this->command->info(
                "✓ Updated " . count($assignedSlotIds) .
                " slots in Atiqah's database ({$occupiedCount} occupied, {$availableCount} available)"
            );

            return $assignedSlotIds;

        } catch (\Exception $e) {
            $this->command->error("Failed to update slots in Atiqah: " . $e->getMessage());
            return [];
        }
    }
}
