<?php

use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\DoctorCalendarController;
use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Strona wyboru lekarza
    Route::get('/doctors', function () {
        $doctors = User::where('role', 'doctor')->get();

        return Inertia::render('Doctor/Index', [
            'doctors' => $doctors,
        ]);
    })->name('doctor.index');

    // Widok kalendarza klienta i rezerwacje
    Route::middleware('auth')->group(function () {
        Route::get('/doctor/{id}/calendar', [DoctorCalendarController::class, 'show'])->name('client.calendar');
        Route::post('/reservations', [DoctorCalendarController::class, 'book']);
    });

    // Zarządzanie dostępnością lekarza
    Route::middleware(['auth', RoleMiddleware::class.':doctor'])->group(function () {
        Route::get('/doctor/availability', [DoctorAvailabilityController::class, 'index'])->name('doctor.availability');
        Route::post('/doctor/availability', [DoctorAvailabilityController::class, 'store']);
        Route::delete('/doctor/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
    });

});
