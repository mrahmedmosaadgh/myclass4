<?php

namespace App\Http\Controllers;

use App\Models\ClassroomSubjectTeacher;
use App\Models\Schedule;
use App\Models\ScheduleCopy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            'cst',
            'cst.classroom',
            'cst.subject',
            'cst.teacher',
        ])
            ->where('copy_id', $active_copy->id)
            // ->where('active', true)
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
            'records2' => $records,
            'options' => $options,
            'active_copy' => $active_copy
        ]);
    }
    public function index2()
    {
        $schedules = Schedule::with(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ->get();


        return response()->json([
            'records' => $schedules,
            'message' => 'Schedules retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000',
                'copy_id' => 'exists:schedule_copies,id'  // Optional in validation since we're setting it
            ]);

            $schedule = Schedule::create($validated);
            $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']);

            return response()->json([
                'message' => 'Schedule created successfully',
                'record' => $schedule
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'store Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Schedule $schedule)
    {
        return response()->json([
            'record' => $schedule->load(['cst.classroom', 'cst.subject', 'cst.teacher']),
            'message' => 'Schedule retrieved successfully'
        ]);
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
            $active_copy = ScheduleCopy::where('active', true)->first();

            // Add copy_id from active copy
            $validated['copy_id'] = $active_copy->id;
            return $validated;
            $schedule->update($validated);

            return response()->json([
                'message' => 'Schedule updated successfully',
                'record' => $schedule->fresh(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'update Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update2(Request $request, Schedule $schedule)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:schedules,id',
                'cst_id' => 'required|exists:classroom_subject_teachers,id',
                'day' => 'required|integer|min:1|max:5',
                'period_number' => 'required|integer|min:1|max:8',
                'active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);
            $active_copy = ScheduleCopy::where('active', true)->first();

            // Add copy_id from active copy
            $validated['copy_id'] = $active_copy->id;
            return $validated;
            $schedule->update($validated);

            return response()->json([
                'message' => 'Schedule updated successfully',
                'record' => $schedule->fresh(['cst.classroom', 'cst.subject', 'cst.teacher'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'update Validation failed',
                'errors' => $e->errors()
            ], 422);
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

    // Optional: Add a method to check for conflicts
    private function checkForConflicts($data)
    {
        $existingSchedule = Schedule::where('day', $data['day'])
            ->where('period_number', $data['period_number'])
            ->whereHas('cst', function ($query) use ($data) {
                $query->where('classroom_id', $data['classroom_id']);
            })
            ->first();

        if ($existingSchedule) {
            return [
                'exists' => true,
                'conflict' => $existingSchedule
            ];
        }

        return ['exists' => false];
    }
}
