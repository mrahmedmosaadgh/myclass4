<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curriculum extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'grade_id',
        'school_id',
        'subject_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    // Relationships
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}