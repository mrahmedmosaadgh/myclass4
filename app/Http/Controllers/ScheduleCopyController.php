<?php

namespace App\Http\Controllers;

use App\Models\ScheduleCopy;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleCopyController extends Controller
{
    public function index()
    {
        $records = ScheduleCopy::with(['school', 'academicYear', 'semester', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(40);

        return Inertia::render('my_class/admin/ScheduleCopies/Index', [
            'records' => $records,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'academicYears' => AcademicYear::select('id', 'name')->get(),
                'semesters' => Semester::select('id', 'name')->get(),
                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schedule_copies')->where(function ($query) use ($request) {
                    return $query->where('school_id', $request->school_id);
                })
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
            'copy_date' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'week_number' => 'nullable|integer|between:1,52',
            'status' => 'required|in:draft,pending,active,archived',
            'metadata' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        // Add created_by field with the authenticated user's ID
        $validated['created_by'] = auth()->id();
        $validated['last_modified_by'] = auth()->id();

        $scheduleCopy = ScheduleCopy::create($validated);

        return response()->json([
            'message' => 'Schedule copy created successfully',
            'record' => $scheduleCopy->load([
                'school',
                'academicYear',
                'semester',
                'createdBy:id,name',
                'lastModifiedBy:id,name'
            ]),
            'status' => 'success'
        ], 201);
    }

    public function update(Request $request, ScheduleCopy $scheduleCopy)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schedule_copies')
                    ->where(function ($query) use ($request) {
                        return $query->where('school_id', $request->school_id);
                    })
                    ->ignore($scheduleCopy->id)
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
            'copy_date' => 'nullable|date',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'week_number' => 'nullable|integer|between:1,52',
            'status' => 'required|in:draft,pending,active,archived',
            'metadata' => 'nullable|json',
            'notes' => 'nullable|string'
        ]);

        // Add last_modified_by field for updates
        $validated['last_modified_by'] = auth()->id();

        $scheduleCopy->update($validated);

        return response()->json([
            'message' => 'Schedule copy updated successfully',
            'record' => $scheduleCopy->load([
                'school',
                'academicYear',
                'semester',
                'createdBy:id,name',
                'lastModifiedBy:id,name'
            ]),
            'status' => 'success'
        ]);
    }

    public function destroy(ScheduleCopy $scheduleCopy)
    {
        $scheduleCopy->delete();
        return redirect()->back()->with('success', 'Schedule copy deleted successfully');
    }
}





