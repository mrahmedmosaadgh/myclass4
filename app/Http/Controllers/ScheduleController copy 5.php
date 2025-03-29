<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleCopy;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        $active_copy = ScheduleCopy::where('active', true)->first();

        if (!$active_copy) {
            return Inertia::render('my_class/admin/Schedules/Index', [
                'records' => [],
                'options' => [],
                'active_copy' => null,
                'error' => 'No active schedule copy found. Please activate one copy.'
            ]);
        }

        $records = Schedule::with([
            'cst.classroom',
            'cst.subject',
            'cst.teacher',
        ])
        ->where('copy_id', $active_copy->id)
        ->where('active', true)
        ->orderBy('period_number')
        ->get();

        $options = [
            'csts' => ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])
                ->get()
                ->map(function ($cst) {
                    return [
                        'id' => $cst->id,
                        'classroom' => [
                            'id' => $cst->classroom->id,
                            'name' => $cst->classroom->name,
                            'grade' => $cst->classroom->grade
                        ],
                        'classroom_name' => $cst->classroom->name,
                        'subject_name' => $cst->subject->name,
                        'teacher_name' => $cst->teacher->name
                    ];
                })
        ];

        return Inertia::render('my_class/admin/Schedules/Index', [
            'records' => $records,
            'options' => $options,
            'active_copy' => $active_copy
        ]);
    }

    public function store(Request $request)
    {
        try {
            // 1. Validate basic input
            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            // 2. Get the active copy
            $activeCopy = ScheduleCopy::where('active', true)->first();
            if (!$activeCopy) {
                return response()->json([
                    'message' => 'No active schedule copy found'
                ], 422);
            }

            // 3. Get CST details and school_id
            $cst = ClassroomSubjectTeacher::with(['classroom', 'subject', 'teacher'])
                ->findOrFail($validated['cst_id']);

            // 4. Add school_id to validated data
            $validated['school_id'] = $cst->classroom->school_id;
            $validated['copy_id'] = $activeCopy->id;

            // 5. Create the schedule
            $schedule = Schedule::create($validated);

            return response()->json([
                'message' => 'Schedule created successfully',
                'record' => $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Schedule $schedule)
    {
        try {
            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            $schedule->update($validated);

            return response()->json([
                'message' => 'Schedule updated successfully',
                'record' => $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->delete();
            return response()->json([
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}















