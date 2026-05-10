<?php

use App\Http\Controllers\EventController;
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
});

require __DIR__.'/settings.php';
