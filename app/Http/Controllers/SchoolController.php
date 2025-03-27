<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\HR;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('hr')->paginate(40);
        $hrs = HR::select('id', 'name')->get();

        return Inertia::render('my_class/admin/Schools/Index', [
            'schools' => $schools,
            'hrs' => $hrs
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'h_r_id' => 'required|exists:h_r_s,id',
        ]);

        School::create($validated);

        return redirect()->back()->with('success', 'School created successfully');
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'h_r_id' => 'required|exists:h_r_s,id',
        ]);

        $school->update($validated);

        return redirect()->back()->with('success', 'School updated successfully');
    }

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->back()->with('success', 'School deleted successfully');
    }
}
