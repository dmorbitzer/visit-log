<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventUserController;
use App\Http\Controllers\TrackingDayController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken($request->username)->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware('auth:sanctum')->group(function () {
    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::patch('/events/{event}', [EventController::class, 'update']);
    Route::patch('/events/{event}/archive', [EventController::class, 'archive']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    // Tracking Days
    Route::get('/events/{event}/days', [TrackingDayController::class, 'index']);
    Route::get('/events/{event}/days/{date}', [TrackingDayController::class, 'show']);
    Route::patch('/events/{event}/days/{date}/track', [TrackingDayController::class, 'track']);
    Route::patch('/events/{event}/days/{date}', [TrackingDayController::class, 'update']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Event Users
    Route::get('/events/{event}/users', [EventUserController::class, 'index']);
    Route::post('/events/{event}/users', [EventUserController::class, 'store']);
    Route::patch('/events/{event}/users/{user}', [EventUserController::class, 'update']);
    Route::delete('/events/{event}/users/{user}', [EventUserController::class, 'destroy']);
});
