<?php

namespace App\Http\Controllers\Concerns\ShelterManagement;

use App\Models\Animal;

trait ViewsDetails
{
    public function getAnimalDetails($id)
    {
        $data = $this->safeQuery(function() use ($id) {
            $animal = Animal::findOrFail($id);

            $medicals = $animal->medicals()->with('vet')->get();
            $vaccinations = $animal->vaccinations()->with('vet')->get();

            $images = collect([]);
            if ($this->isDatabaseAvailable('reporting')) {
                try {
                    $images = $animal->images()->get();
                } catch (\Exception $e) {
                    \Log::warning('Failed to load animal images from eilya database: ' . $e->getMessage());
                }
            }

            return [
                'id' => $animal->id,
                'name' => $animal->name ?? 'Unknown',
                'species' => $animal->species ?? 'Unknown',
                'adoption_status' => $animal->adoption_status ?? 'unknown',
                'health_details' => $animal->health_details ?? 'No health details available.',
                'images' => $images->map(fn($image) => ['id' => $image->id, 'url' => $image->url, 'path' => $image->image_path]),
                'medicals' => $medicals->map(fn($medical) => [
                    'id' => $medical->id,
                    'treatment' => $medical->treatment_type ?? 'N/A',
                    'diagnosis' => $medical->diagnosis ?? 'N/A',
                    'date' => $medical->created_at ? $medical->created_at->format('Y-m-d') : date('Y-m-d'),
                    'cost' => $medical->costs ?? 0,
                    'notes' => $medical->action ?? $medical->remarks ?? '',
                    'vet_name' => $medical->vet->name ?? 'Unknown',
                ]),
                'vaccinations' => $vaccinations->map(fn($vaccination) => [
                    'id' => $vaccination->id,
                    'vaccine_name' => $vaccination->name ?? 'N/A',
                    'date' => $vaccination->created_at ? $vaccination->created_at->format('Y-m-d') : date('Y-m-d'),
                    'next_due_date' => $vaccination->next_due_date ?? null,
                    'batch_number' => $vaccination->type ?? 'N/A',
                    'administered_by' => $vaccination->vet->name ?? 'Unknown',
                    'notes' => $vaccination->remarks ?? '',
                    'cost' => $vaccination->costs ?? 0,
                ]),
            ];
        }, null, 'animals');

        if ($data === null) {
            return response()->json(['error' => 'Failed to load animal details', 'message' => 'Database connection unavailable or animal not found'], 500);
        }

        return response()->json($data);
    }
}
