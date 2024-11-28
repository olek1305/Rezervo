<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DoctorCalendarController extends Controller
{
    /**
     * Index of doctor availabilities for the authenticated doctor.
     */
    public function index(): Response
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403, 'Access denied.');
        }

        $availabilities = DoctorAvailability::where('doctor_id', Auth::id())->get();

        return Inertia::render('Doctor/Calendar', [
            'availabilities' => $availabilities,
            'isDoctor' => Auth::user()->role === 'doctor',
        ]);
    }

    /**
     * Show the calendar for a specific doctor (for clients).
     */
    public function show($id): Response
    {
        $doctor = User::findOrFail($id);

        $availability = DoctorAvailability::where('doctor_id', $id)
            ->whereDate('available_date', '>=', today())
            ->get();

        $reservations = Reservation::where('doctor_id', $id)
            ->whereDate('reservation_date', '>=', today())
            ->get()
            ->groupBy('reservation_date')
            ->map(fn ($res) => $res->pluck('reservation_time')->toArray());

        $timeSlots = collect($availability)->mapWithKeys(function ($slot) {
            $date = (new \DateTime($slot->available_date))->format('Y-m-d');
            $currentTime = new \DateTime($slot->start_time);
            $endTime = new \DateTime($slot->end_time);

            $slots = [];
            while ($currentTime < $endTime) {
                $slots[] = $currentTime->format('H:i');
                $currentTime->modify('+30 minutes');
            }

            return [$date => $slots];
        })->toArray();

        $allDates = collect($availability)->pluck('available_date')->map(fn ($date) => (new \DateTime($date))->format('Y-m-d'));

        $timeSlots = $allDates->mapWithKeys(fn ($date) => [$date => $timeSlots[$date] ?? []])->toArray();
        $reservations = $allDates->mapWithKeys(fn ($date) => [$date => $reservations[$date] ?? []])->toArray();

        return Inertia::render('Client/Calendar', [
            'doctorId' => $id,
            'doctorName' => $doctor->name,
            'availability' => $availability,
            'timeSlots' => $timeSlots,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Store a new availability entry for a doctor.
     */
    public function store(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'available_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        DoctorAvailability::create([
            'doctor_id' => Auth::id(),
            'available_date' => $request->available_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Availability created successfully.'], 201);
    }
}

