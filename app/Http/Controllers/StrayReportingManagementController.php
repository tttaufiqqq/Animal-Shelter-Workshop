<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Image;
use App\Models\AdopterProfile;
use App\Models\AnimalProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Rescue;
use App\Models\User;
use App\Models\Animal;
use App\DatabaseErrorHandler;

class StrayReportingManagementController extends Controller
{
    use DatabaseErrorHandler;
    public function home()
    {
        return view('stray-reporting.main');
    }

    public function indexUser()
    {
        $userReports = $this->safeQuery(
            fn() => Report::where('userID', auth()->id())
                ->with(['images'])
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50)
        );

        $adopterProfile = $this->safeQuery(
            fn() => AdopterProfile::where('adopterID', auth()->id())->first(),
            null
        );

        if (!$adopterProfile) {
            // User has not completed adopter profile → show home but no matches
            $matches = collect();
        } else {
            $matches = $this->safeQuery(
                fn() => AnimalProfile::with('animal')
                    ->when($adopterProfile->preferred_species, function ($q) use ($adopterProfile) {
                        $q->whereHas('animal', fn($a) =>
                            $a->where('species', $adopterProfile->preferred_species)
                        );
                    })
                    ->when($adopterProfile->preferred_size, function ($q) use ($adopterProfile) {
                        $q->where('size', $adopterProfile->preferred_size);
                    })
                    ->get(),
                collect([])
            );
        }

        return view('welcome', compact('userReports', 'adopterProfile', 'matches'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        }

        $validated = $validator->validated();

        $report = Report::create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'report_status' => 'Pending',
            'description' => $validated['description'] ?? null,
            'userID' => Auth::id(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reports', 'public');

                Image::create([
                    'image_path' => $path,
                    'reportID' => $report->id,
                    'animalID' => null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Report submitted successfully!');
    }

    public function index()
    {
        $reports = $this->safeQuery(
            fn() => Report::with('images')
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50)
        );

        return view('stray-reporting.index', compact('reports'));
    }

    public function show($id)
    {
        $report = $this->safeQuery(
            fn() => Report::with(['images', 'rescue.caretaker'])->findOrFail($id),
            null
        );

        if (!$report) {
            return redirect()->route('stray-reporting.index')
                ->with('error', 'Report not found or database connection unavailable.');
        }

        $caretakers = $this->safeQuery(
            fn() => User::role('caretaker')->orderBy('name')->get(),
            collect([])
        );

        return view('stray-reporting.show', compact('report', 'caretakers'));
    }

    public function adminIndex()
    {
        $reports = $this->safeQuery(
            fn() => Report::with(['images', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(50),
            new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50)
        );

        return view('stray-reporting.admin-index', compact('reports'));
    }

    public function destroy($id)
    {
        $report = Report::with('images')->findOrFail($id);

        foreach ($report->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        $report->delete();

        return redirect()->route('stray-reporting.index')->with('success', 'Report deleted successfully!');
    }

    public function assignCaretaker(Request $request, $id)
    {
        $request->validate([
            'caretaker_id' => 'required|exists:taufiq.users,id'  // Cross-database: User on taufiq
        ]);

        $report = Report::findOrFail($id);

        $rescue = Rescue::where('reportID', $report->id)->first();

        if ($rescue) {
            $rescue->update([
                'caretakerID' => $request->caretaker_id
            ]);
        } else {
            Rescue::create([
                'reportID' => $report->id,
                'caretakerID' => $request->caretaker_id,
                'status' => Rescue::STATUS_SCHEDULED,
                'date' => null
            ]);
        }

        $report->update([
            'report_status' => 'In Progress'
        ]);

        return redirect()->back()->with('success', 'Caretaker assigned successfully!');
    }

    public function indexcaretaker(Request $request)
    {
        $rescues = $this->safeQuery(function() use ($request) {
            $query = Rescue::with(['report.images', 'caretaker'])
                ->where('caretakerID', Auth::id());

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            return $query->orderBy('created_at', 'desc')->paginate(50);
        }, new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50));

        return view('stray-reporting.index-caretaker', compact('rescues'));
    }

    public function updateStatusCaretaker(Request $request, $id)
    {
        $rules = [
            'status' => 'required|in:' . implode(',', [
                Rescue::STATUS_SCHEDULED,
                Rescue::STATUS_IN_PROGRESS,
                Rescue::STATUS_SUCCESS,
                Rescue::STATUS_FAILED
            ])
        ];

        if (in_array($request->status, [Rescue::STATUS_SUCCESS, Rescue::STATUS_FAILED])) {
            $rules['remarks'] = 'required|string|min:10|max:1000';
        }

        $request->validate($rules, [
            'remarks.required' => 'Remarks are required for this status.',
            'remarks.min' => 'Remarks must be at least 10 characters.',
            'remarks.max' => 'Remarks must not exceed 1000 characters.',
        ]);

        $rescue = Rescue::where('id', $id)
            ->where('caretakerID', Auth::id())
            ->firstOrFail();

        $updateData = ['status' => $request->status];

        if ($request->filled('remarks')) {
            $updateData['remarks'] = $request->remarks;
        }

        $rescue->update($updateData);

        // Case-insensitive status comparison for cross-RDBMS compatibility
        $currentStatus = strtolower($request->status);
        $successStatus = strtolower(Rescue::STATUS_SUCCESS);
        $failedStatus = strtolower(Rescue::STATUS_FAILED);

        if ($currentStatus === $successStatus || $currentStatus === $failedStatus) {
            $rescue->report->update([
                'report_status' => 'Resolved'
            ]);
        }

        if ($currentStatus === $successStatus) {
            return redirect()
                ->route('animal-management.create', ['rescue_id' => $rescue->id])
                ->with('success', 'Rescue completed! You can now add the animal.');
        }

        return redirect()->back()->with('success', 'Rescue status updated successfully!');
    }

    public function showCaretaker($id)
    {
        $rescue = Rescue::with(['report.images', 'caretaker'])
            ->where('id', $id)
            ->where('caretakerID', Auth::id())
            ->firstOrFail();

        return view('stray-reporting.show-caretaker', compact('rescue'));
    }
}
