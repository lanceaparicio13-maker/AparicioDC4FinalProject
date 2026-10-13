<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResidentController;
use App\Http\Controllers\Api\DocumentRequestController;
use App\Http\Controllers\Api\DocumentTypeController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes for authenticated users
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Resident document requests
    Route::get('/document-requests', [DocumentRequestController::class, 'index']);
    Route::post('/document-requests', [DocumentRequestController::class, 'store']);
    Route::get('/document-requests/{documentRequest}', [DocumentRequestController::class, 'show']);
    Route::delete('/document-requests/{documentRequest}', [DocumentRequestController::class, 'destroy']);

    // Admin-only document request updates
    Route::patch('/document-requests/{documentRequest}', [DocumentRequestController::class, 'update'])
        ->middleware('admin');

    // Document types: authenticated users can view
    Route::get('/document-types', [DocumentTypeController::class, 'index']);
    Route::get('/document-types/{documentType}', [DocumentTypeController::class, 'show']);

    // Admin-only resident management
    Route::middleware('admin')->group(function () {
        Route::get('/residents', [ResidentController::class, 'index']);
        Route::post('/residents', [ResidentController::class, 'store']);
        Route::get('/residents/{resident}', [ResidentController::class, 'show']);
        Route::put('/residents/{resident}', [ResidentController::class, 'update']);
        Route::patch('/residents/{resident}', [ResidentController::class, 'update']);
        Route::delete('/residents/{resident}', [ResidentController::class, 'destroy']);

        // Admin-only document type management
        Route::post('/document-types', [DocumentTypeController::class, 'store']);
        Route::patch('/document-types/{documentType}', [DocumentTypeController::class, 'update']);
        Route::delete('/document-types/{documentType}', [DocumentTypeController::class, 'destroy']);

        // Admin middleware test
        Route::get('/admin-test', function (Request $request) {
            return response()->json([
                'message' => 'Admin access granted.',
                'user' => $request->user(),
            ]);
        });
    });
});
