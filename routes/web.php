<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventUserController;
use App\Http\Controllers\TrackingDayController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('events.index');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::patch('/events/{event}/archive', [EventController::class, 'archive'])->name('events.archive');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::get('/events/{event}/days', [TrackingDayController::class, 'index'])->name('events.days.index');
    Route::get('/events/{event}/days/{date}', [TrackingDayController::class, 'show'])->name('events.days.show');
    Route::patch('/events/{event}/days/{date}/track', [TrackingDayController::class, 'track'])->name('events.days.track');
    Route::patch('/events/{event}/days/{date}', [TrackingDayController::class, 'update'])->name('events.days.update');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/events/{event}/users', [EventUserController::class, 'index'])->name('events.users.index');
    Route::post('/events/{event}/users', [EventUserController::class, 'store'])->name('events.users.store');
    Route::patch('/events/{event}/users/{user}', [EventUserController::class, 'update'])->name('events.users.update');
    Route::delete('/events/{event}/users/{user}', [EventUserController::class, 'destroy'])->name('events.users.destroy');
});

require __DIR__.'/settings.php';
