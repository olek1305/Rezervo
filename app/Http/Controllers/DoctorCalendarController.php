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

    /**
     * Book a reservation for a specific doctor and time slot.
     */
    public function book(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
        ]);

        $isAvailable = DoctorAvailability::where('doctor_id', $request->doctor_id)
            ->where('available_date', $request->reservation_date)
            ->where('start_time', '<=', $request->reservation_time)
            ->where('end_time', '>=', $request->reservation_time)
            ->exists();

        if (!$isAvailable) {
            return response()->json(['error' => 'Selected date and time are not available.'], 422);
        }

        $isAlreadyBooked = Reservation::where('doctor_id', $request->doctor_id)
            ->where('reservation_date', $request->reservation_date)
            ->where('reservation_time', $request->reservation_time)
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json(['error' => 'This time slot is already booked.'], 422);
        }

        $isAlreadyBookedByUser = Reservation::where('doctor_id', $request->doctor_id)
            ->where('user_id', Auth::id())
            ->where('reservation_date', $request->reservation_date)
            ->where('reservation_time', $request->reservation_time)
            ->exists();

        if ($isAlreadyBookedByUser) {
            return response()->json(['error' => 'You have already booked this time slot.'], 422);
        }

        $reservation = Reservation::create([
            'doctor_id' => $request->doctor_id,
            'user_id' => Auth::id(),
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
        ]);

        return response()->json(['message' => 'Reservation created successfully.', 'reservation' => $reservation], 201);
    }
}

