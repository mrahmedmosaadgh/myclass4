<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\PeriodDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->get();

        $classroom = $student->classroom()->with('grade')->first();

        $periodDetails = PeriodDetail::orderBy('period')->get();

        return Inertia::render('Student/Schedule/Index', [
            'schedules' => $schedules,
            'classroom' => $classroom,
            'periodDetails' => $periodDetails
        ]);
    }

    public function currentWeek()
    {
        $student = auth()->user()->student;
        $currentWeek = Carbon::now()->weekOfYear;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $currentWeek)
            ->get();

        return Inertia::render('Student/Schedule/WeekView', [
            'schedules' => $schedules,
            'classroom' => $student->classroom()->with('grade')->first(),
            'periodDetails' => PeriodDetail::orderBy('period')->get(),
            'weekNumber' => $currentWeek,
            'isCurrentWeek' => true
        ]);
    }

    public function nextWeek()
    {
        $student = auth()->user()->student;
        $nextWeek = Carbon::now()->addWeek()->weekOfYear;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $nextWeek)
            ->get();

        return Inertia::render('Student/Schedule/WeekView', [
            'schedules' => $schedules,
            'classroom' => $student->classroom()->with('grade')->first(),
            'periodDetails' => PeriodDetail::orderBy('period')->get(),
            'weekNumber' => $nextWeek,
            'isCurrentWeek' => false
        ]);
    }

    public function getScheduleData(Request $request)
    {
        $student = auth()->user()->student;
        $week = $request->input('week', Carbon::now()->weekOfYear);

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->where('week', $week)
            ->get();

        return response()->json([
            'schedules' => $schedules,
            'periodDetails' => PeriodDetail::orderBy('period')->get()
        ]);
    }

    public function print()
    {
        $student = auth()->user()->student;

        $schedules = Schedule::with(['subject', 'teacher', 'co_teacher', 'teacher_substitute'])
            ->where('classroom_id', $student->classroom_id)
            ->where('active', true)
            ->get();

        $classroom = $student->classroom()->with('grade')->first();
        $periodDetails = PeriodDetail::orderBy('period')->get();

        return Inertia::render('Student/Schedule/Print', [
            'schedules' => $schedules,
            'classroom' => $classroom,
            'periodDetails' => $periodDetails,
            'student' => $student->only(['id', 'name', 'student_id']),
            'printDate' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}

