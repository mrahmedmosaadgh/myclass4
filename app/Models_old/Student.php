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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($student) {
            if ($student->classroom_id) {
                try {
                    $classroom = Classroom::findOrFail($student->classroom_id);

                    // Verify classroom belongs to the selected school
                    if ($student->school_id && $classroom->school_id != $student->school_id) {
                        throw new \Exception("Selected classroom does not belong to the selected school");
                    }

                    // Auto-set stage and grade IDs from classroom
                    $student->stage_id = $classroom->stage_id;
                    $student->grade_id = $classroom->grade_id;

                } catch (ModelNotFoundException $e) {
                    throw new \Exception("Invalid classroom selected");
                }
            }
        });

        static::creating(function ($student) {
            if (empty($student->s_id)) {
                do {
                    $uniqueId = 's' . strtolower(Str::random(4, 'abcdefghijklmnopqrstuvwxyz')) . rand(1000, 9999);
                } while (self::where('s_id', $uniqueId)->exists());

                $student->s_id = $uniqueId;
            }

            $user = User::where('email', $student->s_id)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $student->name,
                    'email' => $student->s_id,
                    'role' => 'student',
                    'password' => bcrypt('12345678'),
                ]);
            }

            $student->user_id = $user->id;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function schoolSection()
    {
        return $this->belongsTo(SchoolSection::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}

