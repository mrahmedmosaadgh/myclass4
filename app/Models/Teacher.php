<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        't_id',
        'school_id',
        'schools_number',
        'school_extra_ids',
        'user_id',
        'name',
        'name_ar',
        'name_cute',
        'national_id',
        'email',
        'phone_number',
        'whatsapp_number',
        'gender',
        'date_of_birth',
        'nationality',
        'address',
        'order_1',
        'order_2',
        'notes',
        'data'
    ];

    protected $casts = [
        'school_extra_ids' => 'array',
        'data' => 'array',
        'date_of_birth' => 'date',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($teacher) {
            // Generate a unique t_id if not provided
            if (empty($teacher->t_id)) {
                do {
                    $uniqueId = 't' . strtolower(Str::random(4, 'abcdefghijklmnopqrstuvwxyz')) . rand(1000, 9999);
                } while (self::where('t_id', $uniqueId)->exists());

                $teacher->t_id = $uniqueId;

            }







            // Check if a user with the given email already exists
            $user = User::where('email', $teacher->t_id)->first();
            // $user = User::where('email', $teacher->email)->first();

            if (!$user) {
                // Create a new user if not exists
                $user = User::create([
                    'name' => $teacher->name,
                    'email' => $teacher->t_id,
                    'role' =>  'teacher' ,
                    // 'email' => $teacher->email,
                    // 'password' => bcrypt(Str::random(10)), // Generate a random password
                    'password' => bcrypt('12345678'), // Generate a random password
                ]);
            }

            $teacher->user_id = $user->id;
        });
    }



    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolsold()
    {
        return $this->belongsTo(School::class)->orWhereIn('id', $this->school_extra_ids ?? []);
    }
    public function schools()
    {
        $primarySchool = $this->belongsTo(School::class, 'school_id')->with(['hr'])->first();
        $extraSchools = School::whereIn('id', $this->school_extra_ids ?? [])->with(['hr'])->get();

        if ($primarySchool) {
            return collect([$primarySchool])->merge($extraSchools);
        } else {
            return $extraSchools;
        }

    }
    // Alternative approach using a custom query scope
    public function scopeWithAllSchools($query)
    {
        return $query->with(['school' => function($query) {
            $query->orWhereIn('id', $this->school_extra_ids ?? []);
        }]);
    }

    // Optional: Add an accessor to get all school names
    public function getSchoolNamesAttribute()
    {
        return School::where('id', $this->school_id)
                    ->orWhereIn('id', $this->school_extra_ids ?? [])
                    ->pluck('name');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}



