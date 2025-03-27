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
        $active_copy = \App\Models\ScheduleCopy::where('active', true)->get();

        if ($active_copy->count() !== 1) {
            return Inertia::render('my_class/admin/Schedules/Index', [
                'records' => [],
                'options' => [],
                'active_copy' => $active_copy,
                'error' => $active_copy->count() === 0
                    ? 'No active schedule copy found. Please activate one copy.'
                    : 'Multiple active schedule copies found. Please ensure only one copy is active.'
            ]);
        }

        $records = Schedule::with(['copy', 'school', 'grade', 'classroom', 'subject', 'teacher'])
            ->where('copy_id', $active_copy->first()->id)
            ->orderBy('classroom_id')
            ->orderBy('period_order')
            ->paginate(40);

        $options = [
            'schools' => \App\Models\School::select('id', 'name')->get(),
            'grades' => \App\Models\Grade::select('id', 'name')->orderBy('name')->get(),
            'classrooms' => \App\Models\Classroom::with('grade')
                ->select('id', 'name', 'grade_id', 'school_id')
                ->orderBy('name')
                ->get(),
            'subjects' => \App\Models\Subject::select('id', 'name')->get(),
            'teachers' => \App\Models\Teacher::select('id', 'name')->get(),
            'copies' => \App\Models\ScheduleCopy::select('id', 'name', 'active')->get(),
        ];

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
            'day' => 'required|integer|min:1|max:7', // Made day required
            'period_order' => 'required|integer|min:1', // Made period_order required
            'place' => 'nullable|string|max:120',
            'color_custom' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        // Check for existing schedule in the same time slot
        $existingSchedule = Schedule::where([
            'copy_id' => $validated['copy_id'],
            'classroom_id' => $validated['classroom_id'],
            'day' => $validated['day'],
            'period_order' => $validated['period_order'],
        ])->first();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'A schedule already exists for this classroom at the specified day and period.',
                'conflict' => $existingSchedule
            ], 422);
        }

        // Check for teacher availability in the same time slot
        $teacherConflict = Schedule::where([
            'copy_id' => $validated['copy_id'],
            'teacher_id' => $validated['teacher_id'],
            'day' => $validated['day'],
            'period_order' => $validated['period_order'],
        ])->first();

        if ($teacherConflict) {
            return response()->json([
                'message' => 'The selected teacher is already scheduled for this time slot.',
                'conflict' => $teacherConflict
            ], 422);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully',
            'record' => $schedule->load(['school', 'grade', 'classroom', 'subject', 'teacher'])
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
            // 'period' => 'nullable|integer|min:-2',
            'period_order' => 'nullable|integer|min:1',
            // 'name' => 'nullable|string|max:120',
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






