<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomSubjectTeacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'grade_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'classes_per_week',
        'data'
    ];

    protected $casts = [
        'data' => 'json',
    ];

    protected $appends = [
        'school_name',
        'grade_name',
        'classroom_name',
        'subject_name',
        'teacher_name'
    ];
//   ?????  // Load all relationships efficiently
// $assignments = ClassroomSubjectTeacher::with([
//     'school:id,name',
//     'grade:id,name',
//     'classroom:id,name',
//     'subject:id,name',
//     'teacher:id,name'
// ])->get();

    // Accessor methods
    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }

    public function getGradeNameAttribute()
    {
        return $this->grade ? $this->grade->name : null;
    }

    public function getClassroomNameAttribute()
    {
        return $this->classroom ? $this->classroom->name : null;
    }

    public function getSubjectNameAttribute()
    {
        return $this->subject ? $this->subject->name : null;
    }

    public function getTeacherNameAttribute()
    {
        return $this->teacher ? $this->teacher->name : null;
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }



    protected static function boot()
    {
        parent::boot();

        static::saving(function ($classroomSubjectTeacher) {
            if ($classroomSubjectTeacher->classroom_id) {
                try {
                    $classroom = Classroom::findOrFail($classroomSubjectTeacher->classroom_id);

                    // Verify classroom belongs to the selected school
                    if ($classroomSubjectTeacher->school_id && $classroom->school_id != $classroomSubjectTeacher->school_id) {
                        throw new \Exception("Selected classroom does not belong to the selected school");
                    }

                    // Auto-set grade ID from classroom
                    $classroomSubjectTeacher->grade_id = $classroom->grade_id;

                } catch (ModelNotFoundException $e) {
                    throw new \Exception("Invalid classroom selected");
                }
            }
        });

    }
}


