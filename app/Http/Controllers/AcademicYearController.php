<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academic_years = AcademicYear::with('school')->paginate(10);
        $schools = School::select('id', 'name')->get();

        return Inertia::render('AcademicYears/Index', [
            'records' => $academic_years,
            'options' => [
                'schools' => $schools
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'start_date' => 'required|date',
            // 'end_date' => 'required|date|after:start_date',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        AcademicYear::create($validated);

        return redirect()->back()->with('success', 'Academic Year created successfully');
    }

    public function update(Request $request, AcademicYear $academic_year)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        $academic_year->update($validated);

        return redirect()->back()->with('success', 'Academic Year updated successfully');
    }

    public function destroy(AcademicYear $academic_year)
    {
        $academic_year->delete();
        return redirect()->back()->with('success', 'Academic Year deleted successfully');
    }

    public function export()
    {
        return response()->download(storage_path('app/academic_years.xlsx'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        // Import logic here

        return redirect()->back()->with('success', 'Academic Years imported successfully');
    }
}
