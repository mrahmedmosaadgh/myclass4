<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory
    // ,
    // SoftDeletes
    ;

    protected $fillable = [
        'copy_id',
        'cst_id',
        'school_id',
        'day',
        'period_number',
        'period_order',
        'active',
        'notes'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = ['teacher_id', 'subject_id', 'classroom_id', 'grade_id'];

    public function getTeacherIdAttribute()
    {
        return $this->cst->teacher_id ?? null;
    }

    public function getSubjectIdAttribute()
    {
        return $this->cst->subject_id ?? null;
    }

    public function getClassroomIdAttribute()
    {
        return $this->cst->classroom_id ?? null;
    }

    public function getGradeIdAttribute()
    {
        return $this->cst->classroom->grade_id ?? null;
    }

    // Make sure to eager load cst relationship to avoid N+1 problems
    protected $with = ['cst'];

    // Relationships
    public function copy()
    {
        return $this->belongsTo(ScheduleCopy::class, 'copy_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function cst()
    {
        return $this->belongsTo(ClassroomSubjectTeacher::class, 'cst_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id')
            ->withDefault(function ($grade, $schedule) {
                // Get grade_id through classroom relationship
                return Grade::find($schedule->cst->classroom->grade_id ?? null);
            });
    }

    public function periodDetail()
    {
        return $this->belongsTo(PeriodDetail::class, 'period_detail_id', 'id');
    }
        // Accessors
        public function getDayNameAttribute()
        {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            return $days[$this->day - 1] ?? null;
        }

    public function scopeWithIds($query)
    {
        return $query->join('classroom_subject_teachers as cst', 'schedules.cst_id', '=', 'cst.id')
                     ->join('classrooms', 'cst.classroom_id', '=', 'classrooms.id')
                     ->addSelect([
                         'schedules.*',
                         'cst.teacher_id as teacher_id',
                         'cst.subject_id as subject_id',
                         'cst.classroom_id as classroom_id',
                         'classrooms.grade_id as grade_id'
                     ]);
    }
}


