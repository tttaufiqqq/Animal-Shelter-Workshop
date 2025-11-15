<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;  
use App\Models\Image;  
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;




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

        return redirect()->route('reports.create')->with('success', 'Report submitted successfully!');
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
        $report = Report::with('images')->findOrFail($id);

        return view('stray-reporting.show', compact('report'));
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

        return redirect()->route('reports.index')->with('success', 'Report deleted successfully!');
    }


    public function updateStatus(Request $request, $id)
   {
        $report = Report::findOrFail($id);

        $request->validate([
            'report_status' => 'required|in:Pending,Approved,In Progress,Resolved,Rejected'
        ]);

        $report->update([
            'report_status' => $request->report_status
        ]);

        return redirect()->back()->with('success', 'Report status updated successfully!');
    } 

}
