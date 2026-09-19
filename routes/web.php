<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventMemberController;
use App\Http\Controllers\EventOwnershipController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/dashboard', '/events')->name('dashboard');
    Route::resource('events', EventController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
    Route::get('/events/{event}/people', [EventMemberController::class, 'index'])->name('events.people');
    Route::patch('/events/{event}/members/{member}', [EventMemberController::class, 'update'])->scopeBindings()->name('events.members.update');
    Route::delete('/events/{event}/members/{member}', [EventMemberController::class, 'destroy'])->scopeBindings()->name('events.members.destroy');
    Route::patch('/events/{event}/ownership', [EventOwnershipController::class, 'update'])->middleware('throttle:6,1')->name('events.ownership.update');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
