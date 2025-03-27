<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Calendar;

class AcademicYearController extends Controller
{
    public function index()
    {
        $schools = School::select('id', 'name')->get();
        $academic_years = AcademicYear::with('school')
            ->select('id', 'name', 'school_id', 'active', 'start_date', 'end_date')
            ->paginate(10);

        return Inertia::render('my_class/admin/AcademicYears/Index', [
            'records' => $academic_years,
            'options' => [
                'schools' => $schools,
                'statusOptions' => [
                    ['value' => true, 'label' => 'Active'],
                    ['value' => false, 'label' => 'Inactive']
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        // Set start date to beginning of the day
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();

        // Calculate end date as start date + 1 year - 1 day
        $endDate = $startDate->copy()->addYear()->subDay();

        // Add the calculated end date to validated data
        $validated['start_date'] = $startDate;
        $validated['end_date'] = $endDate;

        AcademicYear::create($validated);

        return redirect()->back()->with('success', 'Academic Year created successfully');
    }

    public function update(Request $request, AcademicYear $academic_year)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'school_id' => 'required|exists:schools,id',
            'active' => 'boolean'
        ]);

        $academic_year->update($validated);

        return redirect()->back()->with('success', 'Academic Year updated successfully');
    }

    public function destroy(AcademicYear $academic_year)
    {
        $academic_year->delete();
        return redirect()->back()->with('success', 'Academic Year deleted successfully');
    }

    public function export()
    {
        return response()->download(storage_path('app/academic_years.xlsx'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        // Import logic here

        return redirect()->back()->with('success', 'Academic Years imported successfully');
    }

    public function generateCalendar(AcademicYear $academicYear)
    {
        try {
            // Get the school's calendar settings
            $school = $academicYear->school;

            // Set default dates if not provided
            if (!$academicYear->start_date || !$academicYear->end_date) {
                $currentYear = now()->year;
                $academicYear->start_date = Carbon::create($currentYear, 7, 1);
                $academicYear->end_date = Carbon::create($currentYear + 1, 7, 1);
                $academicYear->save();
            }

            // Generate calendar entries for the full year
            $startDate = Carbon::parse($academicYear->start_date);
            $endDate = Carbon::parse($academicYear->end_date);

            $calendars = [];
            $currentDate = $startDate->copy();
            $weekNumber = 1;
            $semesterNumber = 1;
            $skipped = 0;
            $created = 0;

            while ($currentDate <= $endDate) {
                // Check if entry already exists
                $exists = Calendar::where('date', $currentDate->format('Y-m-d'))
                    ->where('school_id', $school->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $currentDate->addDay();
                    continue;
                }

                // Set vacation status for Friday (5) and Saturday (6)
                $isVacation = in_array($currentDate->dayOfWeek, [5, 6]);

                $calendars[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'week' => $weekNumber,
                    'day' => $currentDate->format('l'),
                    'day_number' => $currentDate->dayOfWeek === 0 ? 1 : $currentDate->dayOfWeek + 1, // Convert to 1-7 format where 1 is Sunday
                    'week_number' => $weekNumber,
                    'semester_number' => $semesterNumber,
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'status' => $isVacation ? 0 : 1, // 0 for vacation days, 1 for active days
                    'vacation' => $isVacation
                ];

                $created++;

                // Update counters
                if ($currentDate->dayOfWeek === 5) { // If Friday
                    $weekNumber++;
                    // Optional: Update semester number based on your rules
                    if ($weekNumber > 20) {
                        $semesterNumber = 2;
                    }
                }

                $currentDate->addDay();
            }

            if (!empty($calendars)) {
                Calendar::insert($calendars);
            }

            return response()->json([
                'message' => 'Calendar generated successfully',
                'statistics' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'total' => $created + $skipped
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate calendar: ' . $e->getMessage()
            ], 500);
        }
    }
}






