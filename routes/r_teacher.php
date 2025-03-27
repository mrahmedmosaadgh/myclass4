<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;


// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/home', [TeacherController::class, 'home'])->name('home');
    Route::get('/classes', [TeacherController::class, 'classes'])->name('classes');
    Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance');
    Route::get('/grades', [TeacherController::class, 'grades'])->name('grades');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/home', [StudentController::class, 'home'])->name('home');
    Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
    Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
    Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
});

// Admin routes (you likely already have these)
// Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
//     // ... your existing admin routes
// });
