<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;  
use App\Models\Image;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Rescue;
use App\Models\User;




class StrayReportingManagementController extends Controller
{
    public function home(){
        return view('stray-reporting.main');
    }

    public function create()
    {
        return view('stray-reporting.create');
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
            // Debug: See what's failing
            dd('Validation failed:', $validator->errors()->all(), 'Request data:', $request->all());
        }

        // If validation passes, continue
        $validated = $validator->validated();

        // Create the report
        $report = Report::create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'report_status' => 'Pending',
            'description' => $validated['description'],
            'userID' => Auth::id(),
        ]);

        // Handle image uploads
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

    // Display all reports for the authenticated user
    public function index()
    {
        $reports = Report::with('images') // eager load
            ->orderBy('created_at', 'desc')
            ->paginate(10); // paginate results

        return view('stray-reporting.index', compact('reports'));
    }


    // Display a single report
    public function show($id)
    {
        $report = Report::with(['images', 'rescue.caretaker'])->findOrFail($id);

        // Using Spatie
        $caretakers = User::role('caretaker')->orderBy('name')->get();

        return view('stray-reporting.show', compact('report', 'caretakers'));
    }



    // For admin/staff - view all reports regardless of user
    public function adminIndex()
    {
        $reports = Report::with(['images', 'user']) // Include user relationship
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stray-reporting.admin-index', compact('reports'));
    }

    public function destroy($id)
    {
        $report = Report::with('images')->findOrFail($id);

        // Delete images
        foreach ($report->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        // Delete report
        $report->delete();

        return redirect()->route('stray-reporting.index')->with('success', 'Report deleted successfully!');
    }


    public function assignCaretaker(Request $request, $id)
    {
        $request->validate([
            'caretaker_id' => 'required|exists:users,id'
        ]);

        $report = Report::findOrFail($id);

        // Check if rescue already exists for this report
        $rescue = Rescue::where('reportID', $report->id)->first();

        if ($rescue) {
            // Update existing rescue
            $rescue->update([
                'caretakerID' => $request->caretaker_id
            ]);
        } else {
            // Create new rescue
            Rescue::create([
                'reportID' => $report->id,
                'caretakerID' => $request->caretaker_id,
                'status' => Rescue::STATUS_SCHEDULED,
                'date' => null
            ]);
        }

        // Update report status to 'In Progress' after assigning a caretaker
        $report->update([
            'report_status' => 'In Progress'
        ]);

        return redirect()->back()->with('success', 'Caretaker assigned successfully!');
    }


    public function indexcaretaker(Request $request)
    {
        $query = Rescue::with(['report.images', 'caretaker'])
            ->where('caretakerID', Auth::id());

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $rescues = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('stray-reporting.index-caretaker', compact('rescues'));
    }

   public function updateStatusCaretaker(Request $request, $id)
    {
        // Validate based on status
        $rules = [
            'status' => 'required|in:' . implode(',', [
                Rescue::STATUS_SCHEDULED,
                Rescue::STATUS_IN_PROGRESS,
                Rescue::STATUS_SUCCESS,
                Rescue::STATUS_FAILED
            ])
        ];
        
        // If status is Success or Failed, remarks are required
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

        // Prepare update data
        $updateData = ['status' => $request->status];
        
        // Add remarks if provided
        if ($request->filled('remarks')) {
            $updateData['remarks'] = $request->remarks;
        }

        // Update the Rescue status and remarks
        $rescue->update($updateData);

        // Update related Report status to 'Resolved' if rescue is completed
        if (in_array($request->status, ['Success', Rescue::STATUS_FAILED])) {
            $rescue->report->update([
                'report_status' => 'Resolved'
            ]);
        }

        // Redirect to animal creation page if status is Success
        if ($request->status === Rescue::STATUS_SUCCESS) {
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
