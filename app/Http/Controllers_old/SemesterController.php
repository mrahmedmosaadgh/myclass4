<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::with(['school', 'academicYear'])
            ->paginate(10);
        
        return Inertia::render('Semesters/Index', [
            'records' => $semesters,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'academicYears' => AcademicYear::select('id', 'name')->get()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer|min:1',
            'weeks_number' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        Semester::create($validated);

        return redirect()->back()->with('success', 'Semester created successfully');
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer|min:1',
            'weeks_number' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'school_id' => 'required|exists:schools,id',
            'data' => 'nullable|json',
            'active' => 'boolean'
        ]);

        $semester->update($validated);

        return redirect()->back()->with('success', 'Semester updated successfully');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect()->back()->with('success', 'Semester deleted successfully');
    }
}