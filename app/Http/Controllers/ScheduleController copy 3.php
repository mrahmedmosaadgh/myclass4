<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        $records = Schedule::with(['copy', 'school', 'grade', 'classroom', 'subject', 'teacher'])
            ->orderBy('grade_id')
            ->orderBy('period_order')
            ->paginate(40);  // Increased pagination to show more records at once

        $options = [
            'schools' => \App\Models\School::select('id', 'name')->get(),
            'grades' => \App\Models\Grade::select('id', 'name')->orderBy('name')->get(),
            'classrooms' => \App\Models\Classroom::select('id', 'name')->get(),
            'subjects' => \App\Models\Subject::select('id', 'name')->get(),
            'teachers' => \App\Models\Teacher::select('id', 'name')->get(),
            'copies' => \App\Models\ScheduleCopy::select('id', 'name')->get(),
        ];
        $active_copy = \App\Models\ScheduleCopy::where('active', 1)->get();

        return Inertia::render('my_class/admin/Schedules/Index', [
            'records' => $records,
            'options' => $options,
            'active_copy' => $active_copy
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'copy_id' => 'required|exists:schedule_copies,id',
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'nullable|integer|min:1|max:7',
            'period' => 'nullable|integer|min:-2',
            'num' => 'nullable|integer|min:1',
            'name' => 'nullable|string|max:120',
            'place' => 'nullable|string|max:120',
            'color_custom' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully',
            'record' => $schedule
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'copy_id' => 'required|exists:schedule_copies,id',
            'school_id' => 'required|exists:schools,id',
            'grade_id' => 'required|exists:grades,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day' => 'nullable|integer|min:1|max:7',
            'period' => 'nullable|integer|min:-2',
            'num' => 'nullable|integer|min:1',
            'name' => 'nullable|string|max:120',
            'place' => 'nullable|string|max:120',
            'color_custom' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'record' => $schedule
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully'
        ]);
    }
}




