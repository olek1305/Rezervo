<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        User::factory(10)->create();

        // Create 5 admins
        User::factory(5)->create([
            'role' => 'admin',
        ]);

        // Create 5 doctors
        User::factory(5)->create([
            'role' => 'doctor',
        ])->each(function ($doctor) use ($titles) {
            $doctor->update([
                'specialization' => $titles[array_rand($titles)],
            ]);
        });

        //User
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        //Admin
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        //Doctor
        User::factory()->create([
            'name' => 'doctor User',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'specialization' => 'Cardiologist',
        ]);
    }
}
