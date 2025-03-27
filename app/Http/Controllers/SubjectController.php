<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('school')->paginate(10);
        $schools = School::select('id', 'name')->get();



        return Inertia::render('my_class/admin/Subjects/Index', [
            'records' => Subject::with('school')
                ->latest()
                ->paginate(10),
            'schools' => School::select('id', 'name')
                // ->where('active', true)
                ->get()
        ]);
        // return Inertia::render('Subjects/Index', [
        //     'subjects' => $subjects,
        //     'schools' => $schools
        // ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nour_name' => 'nullable|string|max:255',
            'nour_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'notes' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'Subject created successfully');
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nour_name' => 'nullable|string|max:255',
            'nour_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'notes' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Subject updated successfully');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully');
    }









    public function validateImport(Request $request)
    {
        $statuses = [];

        // Find the ID column (column with is_id = true)
        $idColumn = collect($request->columns)
            ->where('is_id', true)
            ->first();

        // Get visible required columns
        $requiredColumns = collect($request->columns)
            ->where('required', true)
            ->where('hidden', false)
            ->pluck('key')
            ->toArray();

        foreach ($request->data as $row) {
            $status = 'new';

            // Check if record exists by ID field
            if ($idColumn && !empty($row[$idColumn['key']])) {
                $subject = Subject::where('id', $row[$idColumn['key']])->first();
                if ($subject) {
                    $status = 'update';
                    $statuses[] = $status;
                    continue;
                }
            }

            // Check for required fields (only visible ones)
            $missingFields = false;
            foreach ($requiredColumns as $field) {
                if (empty($row[$field])) {
                    $missingFields = true;
                    break;
                }
            }

            if ($missingFields) {
                $status = 'invalid';
                $statuses[] = $status;
                continue;
            }

            // Check if school exists (if school column is visible and required)
            if (in_array('school', $requiredColumns)) {
                $school = School::where('name', $row['school'])->first();
                if (!$school) {
                    $status = 'invalid';
                    $statuses[] = $status;
                    continue;
                }

                // Check for duplicate subject in same school
                if (in_array('name', $requiredColumns)) {
                    $existingSubject = Subject::where('name', $row['name'])
                        ->whereHas('school', function($query) use ($row) {
                            $query->where('name', $row['school']);
                        })
                        ->first();

                    if ($existingSubject) {
                        $status = 'duplicate';
                    }
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

        try {
            DB::beginTransaction();
            $importId = Str::uuid();
            $affectedSubjects = [];

            // Find the ID column
            $idColumn = collect($request->columns)
                ->where('is_id', true)
                ->first();

            // Get visible columns
            $visibleColumns = collect($request->columns)
                ->where('hidden', false)
                ->pluck('key')
                ->toArray();

            foreach ($request->data as $row) {
                try {
                    // Find or create subject
                    $subject = null;

                    // Check for existing subject by ID if provided
                    if ($idColumn && !empty($row[$idColumn['key']])) {
                        $subject = Subject::where($idColumn['key'], $row[$idColumn['key']])->first();
                    }

                    // Only process visible columns
                    $subjectData = [];
                    foreach ($visibleColumns as $column) {
                        if (isset($row[$column])) {
                            // Handle special case for school
                            if ($column === 'school') {
                                $school = School::where('name', $row[$column])->first();
                                if ($school) {
                                    $subjectData['school_id'] = $school->id;
                                }
                            } else {
                                $subjectData[$column] = $row[$column];
                            }
                        }
                    }

                    if ($subject) {
                        // Store original data for undo
                        $affectedSubjects[] = [
                            'id' => $subject->id,
                            'original_data' => $subject->toArray()
                        ];

                        $subject->update($subjectData);
                        $results['success'][] = "Updated subject: {$row['name']}";
                    } else {
                        $subject = Subject::create($subjectData);
                        $affectedSubjects[] = [
                            'id' => $subject->id,
                            'original_data' => null
                        ];
                        $results['success'][] = "Created subject: {$row['name']}";
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing subject {$row['name']}: " . $e->getMessage();
                }
            }

            Cache::put("subject_import_{$importId}", $affectedSubjects, now()->addHours(24));
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



    public function undoImport($importId)
    {
        try {
            DB::beginTransaction();

            $affectedSubjects = Cache::get("subject_import_{$importId}");
            if (!$affectedSubjects) {
                throw new \Exception('Import data not found or expired');
            }

            foreach ($affectedSubjects as $subjectData) {
                $subject = Subject::find($subjectData['id']);
                if (!$subject) continue;

                if ($subjectData['original_data'] === null) {
                    // This was a new record - delete it
                    $subject->delete();
                } else {
                    // This was an update - restore original data
                    $subject->update($subjectData['original_data']);
                }
            }

            Cache::forget("subject_import_{$importId}");
            DB::commit();

            return response()->json(['message' => 'Import successfully undone']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}


