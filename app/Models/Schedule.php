<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'copy_id',
        'school_id',
        'grade_id',
        'classroom_id',
        'subject_id',
        'teacher_id',
        'day',
        'period',
        'num',
        'name',
        'place',
        'color_custom',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'day' => 'integer',
        'period' => 'integer',
        'num' => 'integer',
    ];

    // Relationships
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

    // Accessors
    public function getDayNameAttribute()
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        return $days[$this->day - 1] ?? null;
    }
}
