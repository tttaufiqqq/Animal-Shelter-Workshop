<?php

namespace App\Http\Controllers\Concerns\AnimalManagement;

use App\Models\Animal;
use App\Models\Slot;
use App\Models\Medical;
use App\Models\Vaccination;
use App\Models\Vet;
use App\Models\AnimalProfile;
use App\Models\VisitList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ViewsAnimals
{
    public function index(Request $request)
    {
        $animals = $this->safeQuery(function() use ($request) {
            $with = [];
            if ($this->isDatabaseAvailable('reporting')) { $with[] = 'images'; $with[] = 'rescue'; }
            if ($this->isDatabaseAvailable('shelter')) { $with[] = 'slot'; }

            $query = Animal::with($with);

            if ($request->filled('rescued_by_me') && $request->rescued_by_me === 'true' && Auth::check()) {
                $user = Auth::user();
                if ($user->hasRole('caretaker')) {
                    $rescueIds = $this->safeQuery(
                        fn() => DB::connection('reporting')->table('rescue')->where('caretakerID', $user->id)->pluck('id')->toArray(),
                        [], 'reporting'
                    );
                    $query->when(!empty($rescueIds), fn($q) => $q->whereIn('rescueID', $rescueIds), fn($q) => $q->whereRaw('1 = 0'));
                }
            }

            if ($request->filled('search')) {
                $query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($request->search) . '%');
            }
            if ($request->filled('species')) {
                $query->where(DB::raw('LOWER(species)'), 'LIKE', '%' . strtolower($request->species) . '%');
            }
            if ($request->filled('health_details')) {
                $query->where('health_details', $request->health_details);
            }

            $isAdmin = Auth::check() && Auth::user()->hasRole('admin');
            if (!$isAdmin) {
                $query->where('adoption_status', 'Not Adopted');
            } elseif ($request->filled('adoption_status')) {
                $query->where('adoption_status', $request->adoption_status);
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            $query->orderBy('created_at', 'desc');
            $perPage = ($request->filled('rescued_by_me') && $request->rescued_by_me === 'true' && Auth::check() && Auth::user()->hasRole('caretaker')) ? 50 : 12;

            return $query->paginate($perPage)->appends($request->query());
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12), 'animals');

        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();
            $animalList = $this->safeQuery(function() use ($user) {
                $visitList = VisitList::firstOrCreate(['userID' => $user->id]);
                $animalIds = DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->pluck('animalID')->toArray();
                if (empty($animalIds)) return collect([]);
                if ($this->isDatabaseAvailable('animals')) return Animal::whereIn('id', $animalIds)->get();
                return collect([]);
            }, collect([]), 'booking');
        }

        return view('animal-management.main', compact('animals', 'animalList'));
    }

    public function show($id)
    {
        $eilyaOnline = $this->isDatabaseAvailable('reporting');
        $atiqahOnline = $this->isDatabaseAvailable('shelter');
        $danishOnline = $this->isDatabaseAvailable('booking');

        $with = [];
        if ($eilyaOnline) { $with[] = 'rescue.report'; $with[] = 'images'; }
        if ($atiqahOnline) { $with[] = 'slot.section'; }
        if ($danishOnline) { $with[] = 'bookings.user'; }

        $animal = $this->safeQuery(fn() => Animal::with($with)->findOrFail($id), null, 'animals');

        if (!$animal) {
            return redirect()->route('animal-management.index')->with('error', 'Animal not found or database connection unavailable.');
        }

        $imagesAvailable = $eilyaOnline && $animal->relationLoaded('images');
        $animalImages = $imagesAvailable ? $animal->images : collect([]);
        $hasImages = $animalImages->isNotEmpty();

        $medicals = $this->safeQuery(fn() => Medical::with('vet')->where('animalID', $id)->get(), collect([]), 'animals');
        $vaccinations = $this->safeQuery(fn() => Vaccination::with('vet')->where('animalID', $id)->get(), collect([]), 'animals');
        $vets = $this->safeQuery(fn() => Vet::all(), collect([]), 'animals');

        $slots = $this->safeQuery(
            fn() => Slot::with('section')
                ->where(fn($q) => $q->where('status', 'available')->orWhere('id', $animal->slotID))
                ->orderBy('sectionID')->orderBy('name')->get(),
            collect([]), 'shelter'
        );

        $bookedSlots = collect([]);
        if ($danishOnline && $animal->relationLoaded('bookings')) {
            $bookedSlots = $animal->bookings->map(function($booking) {
                $date = \Carbon\Carbon::parse($booking->appointment_date)->format('Y-m-d');
                $time = \Carbon\Carbon::parse($booking->appointment_time)->format('H:i:s');
                $dateTime = \Carbon\Carbon::parse($date . ' ' . $time);
                return ['date' => $dateTime->format('Y-m-d'), 'time' => $dateTime->format('H:i'), 'datetime' => $dateTime->format('Y-m-d\TH:i')];
            });
        }

        $activeBookings = collect([]);
        $activeBooking = null;
        if ($danishOnline && $animal->relationLoaded('bookings')) {
            $activeBookings = $animal->bookings->whereIn('status', ['Pending', 'Confirmed'])->sortBy([['appointment_date', 'asc'], ['appointment_time', 'asc']]);
            $activeBooking = $activeBookings->first();
        }

        $animalProfile = $this->safeQuery(fn() => AnimalProfile::where('animalID', $id)->first(), null, 'animals');

        $animalList = collect();
        if (Auth::check()) {
            $user = Auth::user();
            $animalList = $this->safeQuery(function() use ($user) {
                $visitList = VisitList::firstOrCreate(['userID' => $user->id]);
                $animalIds = DB::connection('booking')->table('visit_list_animal')->where('listID', $visitList->id)->pluck('animalID')->toArray();
                if (empty($animalIds)) return collect([]);
                if ($this->isDatabaseAvailable('animals')) return Animal::whereIn('id', $animalIds)->get();
                return collect([]);
            }, collect([]), 'booking');
        }

        return view('animal-management.show', compact('animal', 'vets', 'medicals', 'vaccinations', 'slots', 'bookedSlots', 'animalProfile', 'animalList', 'activeBookings', 'activeBooking', 'imagesAvailable', 'animalImages', 'hasImages', 'eilyaOnline', 'atiqahOnline', 'danishOnline'));
    }

    public function assignSlot(Request $request, $animalId)
    {
        try {
            $request->validate(['slot_id' => 'required|exists:shelter.slot,id']);

            $result = $this->procedureService->assignSlot($animalId, $request->slot_id);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $previousSlotId = $result['previous_slot_id'];
            $newSlot = Slot::findOrFail($request->slot_id);
            $newSlotAnimalCount = Animal::where('slotID', $newSlot->id)->count();
            $newSlot->status = $newSlotAnimalCount >= $newSlot->capacity ? 'occupied' : 'available';
            $newSlot->save();

            if ($previousSlotId && $previousSlotId != $newSlot->id) {
                $oldSlot = Slot::find($previousSlotId);
                if ($oldSlot) {
                    $oldSlotAnimalCount = Animal::where('slotID', $oldSlot->id)->count();
                    $oldSlot->status = $oldSlotAnimalCount >= $oldSlot->capacity ? 'occupied' : 'available';
                    $oldSlot->save();
                }
            }

            return back()->with('success', 'Slot assigned successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Animal or Slot not found: ' . $e->getMessage(), ['animal_id' => $animalId, 'slot_id' => $request->slot_id]);
            return back()->with('error', 'Animal or Slot not found. Please check the database connection.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Error assigning slot: ' . $e->getMessage(), ['animal_id' => $animalId, 'slot_id' => $request->slot_id, 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to assign slot: ' . $e->getMessage());
        }
    }
}
