<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calendar extends Model
{
    protected $fillable = [
        'date',
        'week',
        'day',
        'day_number',
        'week_number',
        'semester_number',
        'school_id',
        'academic_year_id',
        'status',
        'event',
        'event_academic',
        'vacation'
    ];

    protected $casts = [
        'date' => 'date',
        'vacation' => 'boolean'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}

