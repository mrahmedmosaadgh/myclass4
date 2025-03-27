<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['school', 'grade', 'classroom', 'subject', 'teacher'])
            ->when($request->search, function($q, $v) {
                $q->where('name', 'like', "%{$v}%")
                  ->orWhereHas('school', fn($q) => $q->where('name', 'like', "%{$v}%"))
                  ->orWhereHas('grade', fn($q) => $q->where('name', 'like', "%{$v}%"));
            })
            ->when($request->school_id, fn($q, $v) => $q->where('school_id', $v))
            ->when($request->grade_id, fn($q, $v) => $q->where('grade_id', $v));

        return Inertia::render('Schedules/Index', [
            'records' => $query->paginate(10),
            'filters' => $request->all(['search', 'school_id', 'grade_id']),
            'schools' => School::select('id', 'name')->get(),
            'grades' => Grade::select('id', 'name')->get(),
            'classrooms' => Classroom::select('id', 'name')->get(),
            'subjects' => Subject::select('id', 'name')->get(),
            'teachers' => Teacher::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|integer|between:1,7',
            'period' => 'required|integer|min:1',
            'place' => 'nullable|string|max:120',
            'color_custom' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully',
            'record' => $schedule->load(['school', 'grade', 'classroom', 'subject', 'teacher'])
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'required|integer|between:1,7',
            'period' => 'required|integer|min:1',
            'place' => 'nullable|string|max:120',
            'color_custom' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'record' => $schedule->fresh(['school', 'grade', 'classroom', 'subject', 'teacher'])
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }
}


