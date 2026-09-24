<?php

use App\Http\Controllers\AcceptEventInvitationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventInvitationController;
use App\Http\Controllers\EventMemberController;
use App\Http\Controllers\EventOverviewController;
use App\Http\Controllers\EventOwnershipController;
use App\Http\Controllers\EventTaskController;
use App\Http\Controllers\EventTaskStatusController;
use App\Http\Controllers\EventVendorController;
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
    Route::get('/events/{event}/overview', EventOverviewController::class)->name('events.overview');
    Route::resource('events.tasks', EventTaskController::class)->only(['index', 'store', 'update', 'destroy'])->scoped();
    Route::resource('events.vendors', EventVendorController::class)->only(['index', 'store', 'update', 'destroy'])->scoped();
    Route::patch('/events/{event}/tasks/{task}/status', [EventTaskStatusController::class, 'update'])->scopeBindings()->name('events.tasks.status');
    Route::get('/events/{event}/people', [EventMemberController::class, 'index'])->name('events.people');
    Route::patch('/events/{event}/members/{member}', [EventMemberController::class, 'update'])->scopeBindings()->name('events.members.update');
    Route::delete('/events/{event}/members/{member}', [EventMemberController::class, 'destroy'])->scopeBindings()->name('events.members.destroy');
    Route::post('/events/{event}/invitations', [EventInvitationController::class, 'store'])->middleware('throttle:10,1')->name('events.invitations.store');
    Route::delete('/events/{event}/invitations/{invitation}', [EventInvitationController::class, 'destroy'])->scopeBindings()->name('events.invitations.destroy');
    Route::patch('/events/{event}/ownership', [EventOwnershipController::class, 'update'])->middleware('throttle:6,1')->name('events.ownership.update');
});

Route::get('/invitations/{token}', [AcceptEventInvitationController::class, 'show'])->middleware('throttle:60,1')->name('invitations.show');
Route::post('/invitations/{token}', [AcceptEventInvitationController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('invitations.accept');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
