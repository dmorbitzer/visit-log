<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('events.index');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
});

require __DIR__.'/settings.php';
