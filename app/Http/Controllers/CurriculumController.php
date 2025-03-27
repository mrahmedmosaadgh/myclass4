<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\School;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $query = Curriculum::with(['school', 'grade', 'subject'])
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($request->school_id, fn($q, $v) => $q->where('school_id', $v))
            ->when($request->grade_id, fn($q, $v) => $q->where('grade_id', $v))
            ->when($request->subject_id, fn($q, $v) => $q->where('subject_id', $v));

        $records = $query->paginate(10);

        if ($request->wantsJson()) {
            return response()->json([
                'records' => $records
            ]);
        }

        return Inertia::render('Curricula/Index', [
            'records' => $records,
            'filters' => $request->all(['search', 'school_id', 'grade_id', 'subject_id']),
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'grades' => Grade::select('id', 'name')->get(),
                'subjects' => Subject::select('id', 'name')->get(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'required|exists:grades,id',
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'active' => 'boolean'
        ]);

        $curriculum = Curriculum::create($validated);

        return response()->json([
            'message' => 'Curriculum created successfully',
            'record' => $curriculum->load(['school', 'grade', 'subject'])
        ]);
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'required|exists:grades,id',
            'school_id' => 'required|exists:schools,id',
            'subject_id' => 'required|exists:subjects,id',
            'active' => 'boolean'
        ]);

        $curriculum->update($validated);

        return response()->json([
            'message' => 'Curriculum updated successfully',
            'record' => $curriculum->fresh(['school', 'grade', 'subject'])
        ]);
    }

    public function destroy(Curriculum $curriculum)
    {
        $curriculum->delete();

        return response()->json([
            'message' => 'Curriculum deleted successfully'
        ]);
    }
}