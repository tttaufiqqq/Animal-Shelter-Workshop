<?php

namespace Database\Seeders\Concerns\AdoptionSeeder;

use Illuminate\Support\Facades\DB;

trait UpdatesAdoptedSlots
{
    private function updateAdoptedSlots(array $affectedSlotIds, bool $atiqahAvailable): void
    {
        if (!$atiqahAvailable) {
            if (!empty($affectedSlotIds)) {
                $this->command->warn("Skipped " . count(array_unique($affectedSlotIds)) . " slot updates (Atiqah offline)");
            }
            return;
        }

        if (empty($affectedSlotIds)) {
            return;
        }

        $uniqueSlots = array_unique($affectedSlotIds);
        $this->command->info("Updating " . count($uniqueSlots) . " slots in Atiqah...");

        $slots = DB::connection('shelter')->table('slot')
            ->whereIn('id', $uniqueSlots)
            ->get(['id', 'capacity'])->keyBy('id');

        $animalCounts = DB::connection('animals')->table('animal')
            ->whereIn('slotID', $uniqueSlots)
            ->selectRaw('slotID, COUNT(*) as cnt')
            ->groupBy('slotID')
            ->pluck('cnt', 'slotID')->toArray();

        $slotsToFree = [];
        foreach ($slots as $slotId => $slot) {
            if (($animalCounts[$slotId] ?? 0) < $slot->capacity) {
                $slotsToFree[] = $slotId;
            }
        }

        if (!empty($slotsToFree)) {
            DB::connection('shelter')->table('slot')
                ->whereIn('id', $slotsToFree)
                ->update(['status' => 'available', 'updated_at' => now()]);
        }
    }
}
