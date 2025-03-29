<?php

namespace App\Http\Controllers;

use App\Models\ClassroomSubjectTeacher;
use App\Models\School;
use App\Models\Grade;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClassroomSubjectTeacherController extends Controller
{
    public function index()
    {
        $records = ClassroomSubjectTeacher::with(['school', 'grade', 'classroom', 'subject', 'teacher'])
            ->paginate(40);

        return Inertia::render('my_class/admin/ClassroomSubjectTeachers/Index', [
            'records' => $records,
            'options' => [
                'schools' => School::select('id', 'name')->get(),
                'grades' => Grade::select('id', 'name')->get(),
                'classrooms' => Classroom::select('id', 'name')->get(),
                'subjects' => Subject::select('id', 'name')->get(),
                'teachers' => Teacher::select('id', 'name')->get(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Get active academic year for the school first
        $activeYear = \App\Models\AcademicYear::where('school_id', $request->school_id)
            ->where('active', true)
            ->firstOrFail();

        // Merge grade_id from classroom before validation
        $classroom = Classroom::findOrFail($request->classroom_id);

        // Create the data array with all required fields
        $data = [
            'school_id' => $request->school_id,
            'academic_year_id' => $activeYear->id,
            'grade_id' => $classroom->grade_id,
            'classroom_id' => $request->classroom_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'classes_per_week' => $request->classes_per_week,
            'data' => ['created_at' => now()->toDateTimeString()]
        ];

        // Validate the data
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classes_per_week' => 'required|integer|min:1',
        ]);

        // Create the record with all required fields
        $record = ClassroomSubjectTeacher::create($data);

        return redirect()->back()->with('success', 'Record created successfully');
    }

    public function update(Request $request, ClassroomSubjectTeacher $classroomSubjectTeacher)
    {
        $classroom = Classroom::findOrFail($request->classroom_id);
        $request->merge(['grade_id' => $classroom->grade_id]);

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classes_per_week' => 'required|integer|min:1',
        ]);

        // Get active academic year for the school
        $activeYear = \App\Models\AcademicYear::where('school_id', $validated['school_id'])
            ->where('active', true)
            ->first();

        if (!$activeYear) {
            return redirect()->back()->with('error', 'No active academic year found for this school');
        }

        // Add academic_year_id to validated data
        $validated['academic_year_id'] = $activeYear->id;

        // Preserve existing data and merge new data
        $existingData = $classroomSubjectTeacher->data ?? [];
        if (is_string($existingData)) {
            $existingData = json_decode($existingData, true) ?? [];
        }
        $validated['data'] = array_merge($existingData, ['updated_at' => now()->toDateTimeString()]);

        $classroomSubjectTeacher->update($validated);

        return redirect()->back()->with('success', 'Record updated successfully');
    }

    public function destroy(ClassroomSubjectTeacher $classroomSubjectTeacher)
    {
        $classroomSubjectTeacher->delete();
        return redirect()->back()->with('success', 'Record deleted successfully');
    }

    public function validateImport(Request $request)
    {
        $statuses = [];

        foreach ($request->data as $row) {
            $status = 'new';

            // Find related models by name
            $school = School::where('name', $row['school'])->first();
            $grade = Grade::where('name', $row['grade'])->first();
            $classroom = Classroom::where('name', $row['classroom'])->first();
            $subject = Subject::where('name', $row['subject'])->first();
            $teacher = Teacher::where('name', $row['teacher'])->first();

            // Check if all required entities exist
            if (!$school || !$grade || !$classroom || !$subject || !$teacher) {
                $status = 'invalid';
            } else {
                // Check if assignment already exists
                $existing = ClassroomSubjectTeacher::where([
                    'school_id' => $school->id,
                    'grade_id' => $grade->id,
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ])->first();

                if ($existing) {
                    $status = 'duplicate';
                }
            }

            $statuses[] = $status;
        }

        return response()->json(['statuses' => $statuses]);
    }

    public function import(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            $importId = Str::uuid();
            $affectedRecords = [];

            foreach ($request->data as $row) {
                // Find related models by name
                $school = School::where('name', $row['school'])->first();
                // $grade = Grade::where('name', $row['grade'])->first();
                $classroom = Classroom::where('name', $row['classroom'])->first();
                $subject = Subject::where('name', $row['subject'])->first();
                $teacher = Teacher::where('name', $row['teacher'])->first();

                // Validate all required entities exist
                if (!$school) {
                    $results['errors'][] = "School not found: {$row['school']}";
                    continue;
                }
                // if (!$grade) {
                //     $results['errors'][] = "Grade not found: {$row['grade']}";
                //     continue;
                // }
                if (!$classroom) {
                    $results['errors'][] = "Classroom not found: {$row['classroom']}";
                    continue;
                }
                if (!$subject) {
                    $results['errors'][] = "Subject not found: {$row['subject']}";
                    continue;
                }
                if (!$teacher) {
                    $results['errors'][] = "Teacher not found: {$row['teacher']}";
                    continue;
                }

                // Check for existing assignment
                $existing = ClassroomSubjectTeacher::where([
                    'school_id' => $school->id,
                    // 'grade_id' => $grade->id,
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                ])->first();

                if ($existing) {
                    // Update existing record
                    $originalData = $existing->toArray();
                    $existing->update([
                        'classes_per_week' => $row['classes_per_week']
                    ]);

                    $affectedRecords[] = [
                        'id' => $existing->id,
                        'original_data' => $originalData
                    ];

                    $results['success'][] = "Updated assignment: {$teacher->name} - {$subject->name} in {$classroom->name}";
                } else {
                    // Create new record
                    $newAssignment = ClassroomSubjectTeacher::create([
                        'school_id' => $school->id,
                        // 'grade_id' => $grade->id,
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'classes_per_week' => $row['classes_per_week']
                    ]);

                    $affectedRecords[] = [
                        'id' => $newAssignment->id,
                        'original_data' => null
                    ];

                    $results['success'][] = "Created new assignment: {$teacher->name} - {$subject->name} in {$classroom->name}";
                }
            }

            // Store import data for potential undo
            Cache::put("classroom_subject_teacher_import_{$importId}", $affectedRecords, now()->addHours(24));

            DB::commit();
            return response()->json([
                'results' => $results,
                'importId' => $importId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => [],
                'errors' => ['An error occurred while processing the data: ' . $e->getMessage()]
            ], 500);
        }
    }
}





