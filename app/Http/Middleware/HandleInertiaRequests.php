<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'user_role' => $request->user()->role,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames(),
                    'school' => $this->getUserSchool($request->user()),
                    'classroom' => $this->getUserClassroom($request->user()),
                ] : null,
            ],
            // ... other shared data
        ]);
    }

    private function getUserSchool($user)
    {
        if (!$user) return null;

        if ($user->role === 'teacher') {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            return $teacher ? $teacher->school : null;
        }

        if ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)->first();
            return $student ? $student->school : null;
        }

        return null;
    }

    private function getUserClassroom($user)
    {
        if (!$user) return null;

        if ($user->role === 'teacher') {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)
                ->with(['classrooms' => function($query) {
                    $query->select('classrooms.id', 'name', 'school_id');
                }])
                ->first();
            return $teacher ? $teacher->classrooms : null;
        }

        if ($user->role === 'student') {
            $student = \App\Models\Student::where('user_id', $user->id)
                ->with(['classroom' => function($query) {
                    $query->select('id', 'name', 'school_id');
                }])
                ->first();
            return $student ? $student->classroom : null;
        }

        return null;
    }
}





