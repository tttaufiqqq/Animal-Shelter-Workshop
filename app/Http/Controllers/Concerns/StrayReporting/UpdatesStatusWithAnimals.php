<?php

namespace App\Http\Controllers\Concerns\StrayReporting;

use App\Models\Report;
use App\Models\Rescue;
use App\Models\Animal;
use App\Models\Image;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait UpdatesStatusWithAnimals
{
    public function updateStatusWithAnimals(Request $request, $id)
    {
        if (!$this->isDatabaseAvailable('shelter')) {
            return response()->json(['success' => false, 'message' => 'Cannot add animals: Shelter Management database (slots) is currently offline. Animals must be assigned to shelter slots. Please try again later.'], 503);
        }

        if (!$this->isDatabaseAvailable('animals')) {
            return response()->json(['success' => false, 'message' => 'Cannot add animals: Animal Management database is currently offline. Please try again later.'], 503);
        }

        if (!$this->isDatabaseAvailable('reporting')) {
            return response()->json(['success' => false, 'message' => 'Cannot update rescue: Rescue database is currently offline. Please try again later.'], 503);
        }

        DB::connection('reporting')->beginTransaction();
        DB::connection('animals')->beginTransaction();

        $uploadedFiles = [];

        try {
            $rescue = Rescue::where('id', $id)->where('caretakerID', Auth::id())->firstOrFail();

            $request->validate([
                'status' => 'required|in:Success',
                'remarks' => 'required|string|min:10|max:1000',
                'animals' => 'required|json',
            ]);

            $animalsData = json_decode($request->animals, true);

            if (empty($animalsData)) {
                throw new \Exception('No animals data provided');
            }

            $oldRescueStatus = $rescue->status;
            $rescue->update(['status' => $request->status, 'remarks' => $request->remarks]);

            $oldReportStatus = $rescue->report->report_status;
            $rescue->report->update(['report_status' => Report::STATUS_COMPLETED]);

            AuditService::logRescue('report_status_synced_completed_with_animals', $rescue->id,
                ['report_status' => $oldReportStatus],
                ['report_status' => Report::STATUS_COMPLETED],
                [
                    'report_id' => $rescue->reportID, 'rescue_status' => $request->status, 'rescue_final_status' => 'Success',
                    'caretaker_name' => Auth::user()->name, 'address' => $rescue->report->address ?? 'Unknown',
                    'sync_trigger' => 'Rescue completed successfully with animals added', 'animals_count' => count($animalsData),
                ]
            );

            foreach ($animalsData as $index => $animalData) {
                $animal = Animal::create([
                    'name' => $animalData['name'],
                    'species' => $animalData['species'],
                    'gender' => $animalData['gender'],
                    'age' => $animalData['age'],
                    'weight' => $animalData['weight'],
                    'health_details' => $animalData['health_details'],
                    'adoption_status' => $animalData['adoption_status'],
                    'rescueID' => $animalData['rescueID'],
                    'slotID' => $animalData['slotID'] ?? null,
                ]);

                $imageFiles = $request->file("animal_{$index}_images", []);

                foreach ($imageFiles as $imageFile) {
                    $uploadResult = cloudinary()->uploadApi()->upload($imageFile->getRealPath(), [
                        'folder' => 'animals',
                        'public_id' => pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME),
                    ]);

                    $path = $uploadResult['public_id'];
                    $uploadedFiles[] = $path;

                    Image::create(['image_path' => $path, 'animalID' => $animal->id, 'reportID' => null]);
                }
            }

            AuditService::logRescue('rescue_completed_with_animals', $rescue->id,
                ['status' => $rescue->status],
                ['status' => 'Success', 'animals_added' => count($animalsData)],
                ['report_id' => $rescue->reportID, 'caretaker_name' => Auth::user()->name, 'animal_count' => count($animalsData)]
            );

            DB::connection('reporting')->commit();
            DB::connection('animals')->commit();

            session()->flash('success', 'Rescue completed successfully! ' . count($animalsData) . ' animal(s) added to the shelter.');

            return response()->json([
                'success' => true,
                'message' => 'Rescue completed successfully! ' . count($animalsData) . ' animal(s) added to the shelter.',
                'redirect' => route('rescues.show', $rescue->id),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::connection('reporting')->rollBack();
            DB::connection('animals')->rollBack();

            foreach ($uploadedFiles as $filePath) {
                try { cloudinary()->uploadApi()->destroy($filePath); } catch (\Exception $e) {}
            }

            return response()->json(['success' => false, 'message' => 'Rescue not found or you are not authorized.'], 404);

        } catch (\Exception $e) {
            DB::connection('reporting')->rollBack();
            DB::connection('animals')->rollBack();

            foreach ($uploadedFiles as $filePath) {
                try { cloudinary()->uploadApi()->destroy($filePath); } catch (\Exception $e) {}
            }

            \Log::error('Error updating rescue with animals: ' . $e->getMessage(), [
                'rescue_id' => $id, 'user_id' => Auth::id(), 'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to complete rescue: ' . $e->getMessage()], 500);
        }
    }
}
