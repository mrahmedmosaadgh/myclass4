<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\PermissionsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/permissions', [PermissionsController::class, 'index'])->name('admin.permissions');
    Route::post('/permissions', [PermissionsController::class, 'store'])->name('permissions.store');
    Route::put('/permissions/{permission}', [PermissionsController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])->name('permissions.destroy');

    // Roles
    Route::post('/roles', [PermissionsController::class, 'storeRole'])->name('admin.roles.store');
    Route::put('/roles/{role}', [PermissionsController::class, 'updateRole'])->name('admin.roles.update');
    Route::delete('/roles/{role}', [PermissionsController::class, 'destroyRole'])->name('admin.roles.destroy');

    // Users
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::put('/users/{user}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('admin.users.reset-password');

            // Add the missing route for updating user roles
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('admin.users.roles.update');

    Route::resource('documentation', DocumentationController::class);
    // Route::resource('documentation', DocumentationController::class);
    // Route::resource('documentation', DocumentationController::class)
    //      ->names([
    //          'index' => 'documentation.index',
    //          'create' => 'documentation.create',
    //          'store' => 'documentation.store',
    //          'show' => 'documentation.show',
    //          'edit' => 'documentation.edit',
    //          'update' => 'documentation.update',
    //          'destroy' => 'documentation.destroy',
    //      ]);

});







