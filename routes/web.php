<?php

use App\Http\Controllers\AdminDoctorController;
use App\Http\Controllers\DoctorAvailabilityController;
use App\Http\Controllers\DoctorCalendarController;
use App\Http\Controllers\ReservationController;
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

// Doctor's Choice Page
    Route::get('/doctors', function () {
        $doctors = User::where('role', 'doctor')
            ->whereHas('availabilities')
            ->get(['id', 'name', 'specialization']);

        return Inertia::render('Doctor/Index', [
            'doctors' => $doctors,
        ]);
    })->name('doctor.index');

    // Customer calendar view and bookings
    Route::middleware('auth')->group(function () {
        Route::get('/reservations', [ReservationController::class, 'index']);
        Route::get('/doctor/{id}/calendar', [DoctorCalendarController::class, 'show'])->name('client.calendar');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservation.store');
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    });

    // Management of physician availability
    Route::middleware(['auth', RoleMiddleware::class.':doctor'])->group(function () {
        Route::get('/doctor/availability', [DoctorAvailabilityController::class, 'index'])->name('doctor.availability');
        Route::post('/doctor/availability', [DoctorAvailabilityController::class, 'store']);
        Route::delete('/doctor/availability/{id}', [DoctorAvailabilityController::class, 'destroy']);
    });

    Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
        Route::post('/doctors/{user}/assign', [AdminDoctorController::class, 'assignRole'])->name('doctors.assign');
        Route::post('/doctors/{user}/remove', [AdminDoctorController::class, 'removeRole'])->name('doctors.remove');
    });
});
