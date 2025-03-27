<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/users', [UserController::class, 'index'])->name('users.index');
//     Route::post('/users', [UserController::class, 'store'])->name('users.store');
//     Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
//     Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
//     Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
// });


include dirname(__DIR__) . '/routes/admin.php';
include dirname(__DIR__) . '/routes/r_hr.php';
include dirname(__DIR__) . '/routes/r_teacher.php';
