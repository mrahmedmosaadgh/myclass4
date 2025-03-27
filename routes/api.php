<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Api\ImageUploadController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        Route::get('/classes', [TeacherController::class, 'getTeacherClasses']);
        Route::get('/students', [TeacherController::class, 'getStudents']);
    });
});

Route::post('/upload-image', [ImageUploadController::class, 'store'])
    ->middleware(['auth:sanctum']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'user' => [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'user_role' => $request->user()->role,
            'email' => $request->user()->email,
            'roles' => $request->user()->getRoleNames(),
            'school' => app(HandleInertiaRequests::class)->getUserSchool($request->user()),
            'classroom' => app(HandleInertiaRequests::class)->getUserClassroom($request->user()),
        ]
    ]);
});

