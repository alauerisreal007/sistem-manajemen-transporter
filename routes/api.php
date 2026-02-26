<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Login via same AuthenticatedSessionController->store (returns token JSON when request expects JSON)
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Protected API routes using sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // // Superadmin can create admins/users
    // Route::post('/superadmin/users', [\App\Http\Controllers\Api\SuperAdminUserController::class, 'store'])->middleware('role:superadmin');
});
