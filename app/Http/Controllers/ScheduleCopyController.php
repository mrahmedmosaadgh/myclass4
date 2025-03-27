<?php

namespace App\Http\Controllers;

use App\Helpers\AcademicHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleCopy;
use App\Models\Schedule;
use App\Models\ClassroomSubjectTeacher;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ScheduleCopyController extends Controller
{
    public function index()
    {
        $records = ScheduleCopy::with(['school', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->paginate(40);

        if (request()->wantsJson()) {
            return response()->json([
                'records' => $records
            ]);
        }

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
            'week_number' => 'nullable|integer|min:1|max:52',
            'status' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);




                // If academic_year_id is not provided, use active academic year
                if (!isset($validated['academic_year_id'])) {
                    $activeYear = AcademicHelper::getActiveAcademicYear($validated['school_id']);
                    $validated['academic_year_id'] = $activeYear?->id;
                }

                // If semester_id is not provided, use active semester
                if (!isset($validated['semester_id'])) {
                    $activeSemester = AcademicHelper::getActiveSemester(
                        $validated['school_id'],
                        $validated['academic_year_id']
                    );
                    $validated['semester_id'] = $activeSemester?->id;
                }

                $validated['created_by'] = auth()->id();
                $validated['copy_date'] = now();

        $scheduleCopy = ScheduleCopy::create($validated);

        return response()->json([
            'message' => 'Schedule copy created successfully',
            'record' => $scheduleCopy
        ]);
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
                    ->ignore($scheduleCopy->id, 'id')  // Explicitly specify the column name 'id'
            ],
            'description' => 'nullable|string',
            'copy_date' => 'nullable|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $scheduleCopy->update($validated);

        return response()->json([
            'message' => 'Schedule copy updated successfully',
            'record' => $scheduleCopy
        ]);
    }

    public function destroy(ScheduleCopy $scheduleCopy)
    {
        $scheduleCopy->delete();

        return response()->json([
            'message' => 'Schedule copy deleted successfully'
        ]);
    }

    public function checkScheduleChanges($id)
    {
        try {
            $copy = ScheduleCopy::with([
                'school',
                'academicYear',
                'semester'
            ])->findOrFail($id);

            // Check if copy is in pending status
            if ($copy->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending copies can be used to create schedules',
                    'status' => 'error'
                ], 422);
            }

            // Get all classroom_subject_teachers for this school
            $csts = ClassroomSubjectTeacher::where('school_id', $copy->school_id)
                ->where('academic_year_id', $copy->academic_year_id)
                // ->where('semester_id', $copy->semester_id)
                ->with(['classroom', 'subject', 'teacher'])
                ->get();

            // Get existing schedules for this copy
            $existingSchedules = Schedule::where('copy_id', $copy->id)->get();

            // Create collections of unique identifiers for comparison
            $existingIdentifiers = $existingSchedules->map(function ($schedule) {
                return [
                    'identifier' => "{$schedule->school_id}_{$schedule->classroom_id}_{$schedule->subject_id}_{$schedule->teacher_id}",
                    'details' => [
                        'classroom' => $schedule->classroom->name,
                        'subject' => $schedule->subject->name,
                        'teacher' => $schedule->teacher->name
                    ]
                ];
            })->pluck('details', 'identifier')->toArray();

            $newIdentifiers = $csts->map(function ($cst) {
                return [
                    'identifier' => "{$cst->school_id}_{$cst->classroom_id}_{$cst->subject_id}_{$cst->teacher_id}",
                    'details' => [
                        'classroom' => $cst->classroom->name,
                        'subject' => $cst->subject->name,
                        'teacher' => $cst->teacher->name
                    ]
                ];
            })->pluck('details', 'identifier')->toArray();

            // Calculate differences
            $toDelete = array_diff_key($existingIdentifiers, $newIdentifiers);
            $toCreate = array_diff_key($newIdentifiers, $existingIdentifiers);

            return response()->json([
                'status' => 'success',
                'changes' => [
                    'to_delete' => [
                        'count' => count($toDelete),
                        'items' => $toDelete
                    ],
                    'to_create' => [
                        'count' => count($toCreate),
                        'items' => $toCreate
                    ],
                    'unchanged' => count(array_intersect_key($existingIdentifiers, $newIdentifiers))
                ],
                'copy_id' => $id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check schedule changes: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function executeScheduleChanges($id)
    {
        try {
            DB::beginTransaction();

            $copy = ScheduleCopy::with([
                'school',
                'academicYear',
                'semester'
            ])->findOrFail($id);

            if ($copy->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending copies can be used to create schedules',
                    'status' => 'error'
                ], 422);
            }

            // Get current data
            $csts = ClassroomSubjectTeacher::where('school_id', $copy->school_id)
                ->where('academic_year_id', $copy->academic_year_id)
                ->with(['classroom', 'subject', 'teacher'])
                ->get();

            // Add debug logging
            Log::info('CST Records:', $csts->toArray());

            $existingSchedules = Schedule::where('copy_id', $copy->id)->get();

            // Create identifier arrays
            $existingIdentifiers = $existingSchedules->map(function ($schedule) {
                return "{$schedule->school_id}_{$schedule->classroom_id}_{$schedule->subject_id}_{$schedule->teacher_id}";
            })->toArray();

            $newIdentifiers = $csts->map(function ($cst) {
                return "{$cst->school_id}_{$cst->classroom_id}_{$cst->subject_id}_{$cst->teacher_id}";
            })->toArray();

            // Delete old records
            foreach ($existingSchedules as $schedule) {
                $identifier = "{$schedule->school_id}_{$schedule->classroom_id}_{$schedule->subject_id}_{$schedule->teacher_id}";
                if (!in_array($identifier, $newIdentifiers)) {
                    $schedule->delete();
                }
            }

            // Create new records
            foreach ($csts as $cst) {
                $identifier = "{$cst->school_id}_{$cst->classroom_id}_{$cst->subject_id}_{$cst->teacher_id}";
                if (!in_array($identifier, $existingIdentifiers)) {
                    // Add debug logging
                     Log::info('Processing CST:', [
                        'id' => $cst->id,
                        'identifier' => $identifier,
                        'classes_per_week' => $cst->classes_per_week
                    ]);

                    if (!isset($cst->classes_per_week) || !is_numeric($cst->classes_per_week) || $cst->classes_per_week < 1) {
                        throw new \Exception("Invalid classes_per_week value for classroom: {$cst->classroom->name}, subject: {$cst->subject->name}, teacher: {$cst->teacher->name}");
                    }

                    // Create multiple entries based on classes_per_week
                    for ($i = 1; $i <= $cst->classes_per_week; $i++) {
                        $scheduleData = [
                            'copy_id' => $copy->id,
                            'cst_id' => $cst->id,  // Ensure this is not null
                            'school_id' => $cst->school_id,
                            'grade_id' => $cst->classroom->grade_id,
                            'classroom_id' => $cst->classroom_id,
                            'subject_id' => $cst->subject_id,
                            'teacher_id' => $cst->teacher_id,
                            'week' => $copy->week_number,
                            'semester' => $copy->semester_id,
                            'period_order' => $i,
                            'active' => true
                        ];

                        // Add debug logging
                        Log::info('Creating Schedule with data:', $scheduleData);

                        Schedule::create($scheduleData);
                    }
                }
            }

            // Update copy status
            $copy->update([
                'status' => 'active',
                'activated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Schedule changes executed successfully',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to execute schedule changes: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}









