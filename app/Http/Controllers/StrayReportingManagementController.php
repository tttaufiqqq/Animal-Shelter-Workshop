<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;  
use Illuminate\Support\Facades\Auth;


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
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'report_status' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $report = Report::create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'report_status' => $request->report_status,
            'description' => $request->description,
            'userID' => Auth::id(),
        ]);

        // if you want to handle images later, you can attach them here

        return redirect()->route('reports.create')->with('success', 'Report submitted successfully!');
    }
}
