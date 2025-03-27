<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleCopy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'active',
        'copy_date',
        'academic_year_id',
        'semester_id',
        'week_number',
        'status',
        'activated_at',
        'metadata',
        'notes',
        'created_by',
        'last_modified_by'
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
        'copy_date' => 'date',
        'activated_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }
}
