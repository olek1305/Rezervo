<?php

namespace Database\Seeders;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $titles = [
            'Dermatologist',
            'Endocrinologist',
            'Gastroenterologist',
            'Neurologist',
            'Orthopedic Surgeon',
            'Pediatrician',
            'Psychiatrist',
            'Radiologist',
            'Physiotherapist',
        ];

        // Create regular users
        User::factory(100)->create();

        // Create admins
        User::factory(5)->create([
            'role' => 'admin',
        ]);

        // Create doctors with specializations
        $doctors = User::factory(10)->create([
            'role' => 'doctor',
        ])->each(function ($doctor) use ($titles) {
            $doctor->update([
                'specialization' => $titles[array_rand($titles)],
            ]);

            // Generate availability for each doctor
            $this->generateDoctorAvailability($doctor->id);
        });

        // Create specific users for testing
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $testDoctor = User::factory()->create([
            'name' => 'doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'specialization' => 'Cardiologist',
        ]);

        // Generate availability for test doctor
        $this->generateDoctorAvailability($testDoctor->id);

        // Generate reservations for users
        $this->generateReservations();
    }

    /**
     * Generate availability for a doctor.
     */
    private function generateDoctorAvailability(int $doctorId): void
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(40);

        while ($startDate->lte($endDate)) {
            DoctorAvailability::create([
                'doctor_id' => $doctorId,
                'available_date' => $startDate->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
            ]);

            $startDate->addDay();
        }
    }

    /**
     * Generate reservations for users based on availability.
     */
    private function generateReservations(): void
    {
        $users = User::where('role', 'user')->get();
        $availabilities = DoctorAvailability::all();

        foreach ($availabilities as $availability) {
            $time = Carbon::parse($availability->start_time);
            $endTime = Carbon::parse($availability->end_time);

            while ($time->lt($endTime)) {
                // We randomly decide whether a particular date will be booked
                if (rand(1, 100) <= 70) {
                    $user = $users->random();

                    // Checking whether a given appointment is already occupied
                    $isReserved = Reservation::where([
                        ['doctor_id', $availability->doctor_id],
                        ['reservation_date', $availability->available_date],
                        ['reservation_time', $time->toTimeString()],
                    ])->exists();

                    if (!$isReserved) {
                        Reservation::create([
                            'doctor_id' => $availability->doctor_id,
                            'user_id' => $user->id,
                            'reservation_date' => $availability->available_date,
                            'reservation_time' => $time->toTimeString(),
                        ]);
                    }
                }

                $time->addMinutes(30);
            }
        }
    }
}
