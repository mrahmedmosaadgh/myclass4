<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeacherController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        Route::get('/classes', [TeacherController::class, 'getTeacherClasses']);
        Route::get('/students', [TeacherController::class, 'getStudents']);
    });
});

