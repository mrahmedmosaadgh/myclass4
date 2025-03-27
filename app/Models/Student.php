<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        's_id',
        'name',
        'name_ar',
        'name_cute',
        'order_1',
        'order_2',
        'notes',
        'user_id',
        'parent_id',
        'school_section_id',
        'school_id',
        'data',
        'classroom_id',
        'stage_id',
        'grade_id',
        'classroom_history'
    ];

    protected $casts = [
        'data' => 'array',
        'classroom_history' => 'array'
    ];

    protected $appends = [
        'classroom_name',
        'stage_name',
        'grade_name',
        'parent_name',
        'school_name'
    ];

    // Define all relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Accessor methods
    public function getSchoolNameAttribute()
    {
        return $this->school ? $this->school->name : null;
    }

    public function getStageNameAttribute()
    {
        return $this->stage ? $this->stage->name : null;
    }

    public function getGradeNameAttribute()
    {
        return $this->grade ? $this->grade->name : null;
    }

    public function getClassroomNameAttribute()
    {
        return $this->classroom ? $this->classroom->name : null;
    }

    public function getParentNameAttribute()
    {
        return $this->parent ? $this->parent->name : null;
    }
}



