<?php

namespace App\Http\Controllers;

use App\Models\ScheduleCopy;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleCopyController extends Controller
{
    public function index()
    {
        $records = ScheduleCopy::with(['school', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $options = [
            'schools' => School::select('id', 'name')->get(),
            'academicYears' => AcademicYear::select('id', 'name')->get(),
            'semesters' => Semester::select('id', 'name')->get(),
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'archived', 'label' => 'Archived'],
            ]
        ];

        return Inertia::render('ScheduleCopies/Index', [
            'records' => $records,
            'options' => $options
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'copy_date' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'week_number' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,pending,active,archived',
            'metadata' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        $validated['created_by'] = auth()->id();
        
        ScheduleCopy::create($validated);

        return redirect()->back()->with('success', 'Schedule copy created successfully.');
    }

    public function update(Request $request, ScheduleCopy $scheduleCopy)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'copy_date' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'week_number' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,pending,active,archived',
            'metadata' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        $validated['last_modified_by'] = auth()->id();

        $scheduleCopy->update($validated);

        return redirect()->back()->with('success', 'Schedule copy updated successfully.');
    }

    public function destroy(ScheduleCopy $scheduleCopy)
    {
        $scheduleCopy->delete();
        return redirect()->back()->with('success', 'Schedule copy deleted successfully.');
    }
}