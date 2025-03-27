<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nour_name',
        'nour_id',
        'description',
        'active',
        'notes',
        'school_id'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];
    protected $table = 'subjects';

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
