<?php

use App\Models\User;
use Carbon\Carbon;
use App\Models\DoctorAvailability;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReservationsDeletedNotification;

test('Admin assigns the role of doctor and a specialization to a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $user = User::factory()->create([
        'role' => 'doctor',
        'specialization' => 'Cardiologist',
    ]);

    $admin->assignDoctorRole($user, 'Cardiologist');

    expect($user->role)->toBe('doctor')
        ->and($user->specialization)->toBe('Cardiologist');
});

test('Doctor creates availability schedule with specific date and time', function () {
    $doctor = User::factory()->create([
        'role' => 'doctor',
        'specialization' => 'Dermatologist',
    ]);

    $testDate = Carbon::now()->addDay();

    $availability = DoctorAvailability::create([
        'doctor_id' => $doctor->id,
        'available_date' => $testDate->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    expect($availability->doctor_id)->toBe($doctor->id)
        ->and($availability->available_date)->toBe($testDate->toDateString())
        ->and($availability->start_time)->toBe('09:00:00')
        ->and($availability->end_time)->toBe('17:00:00');
});

test('Doctor is demoted, availability is deleted, and notification is sent', function () {
    Notification::fake();

    $doctor = User::factory()->create([
        'role' => 'doctor',
        'specialization' => 'Pediatrician',
    ]);

    DoctorAvailability::create([
        'doctor_id' => $doctor->id,
        'available_date' => now()->addDay()->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $doctor->update(['role' => 'user', 'specialization' => null]);
    DoctorAvailability::where('doctor_id', $doctor->id)->delete();

    expect($doctor->role)->toBe('user')
        ->and($doctor->specialization)->toBeNull()
        ->and(DoctorAvailability::where('doctor_id', $doctor->id)->count())->toBe(0);

    $doctor->notify(new ReservationsDeletedNotification($doctor->name, 'Pediatrician'));

    Notification::assertSentTo(
        $doctor,
        ReservationsDeletedNotification::class,
        function ($notification) use ($doctor) {
            return $notification->doctorName === $doctor->name &&
                $notification->specialization === 'Pediatrician';
        }
    );
});


