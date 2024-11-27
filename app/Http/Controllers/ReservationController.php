<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    /**
     * Fetch reservations for a specific doctor within a date range.
     */
    public function getReservationsForDoctor(Request $request): JsonResponse
    {
        $doctorId = $request->query('doctor_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $reservations = Reservation::where('doctor_id', $doctorId)
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->get();

        return response()->json($reservations);
    }

    /**
     * Store a new reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i', // Dodano czas
        ]);

        $doctorId = $request->doctor_id;
        $reservationDate = $request->reservation_date;
        $reservationTime = $request->reservation_time;

        // Check if the doctor is available on the selected date
        $availability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('available_date', $reservationDate)
            ->where('start_time', '<=', $reservationTime)
            ->where('end_time', '>', $reservationTime) // Sprawdzenie przedziału czasowego
            ->first();

        if (!$availability) {
            return response()->json(['error' => 'The doctor is not available on this date and time.'], 400);
        }

        // Check if the time slot is already booked
        $isAlreadyBooked = Reservation::where('doctor_id', $doctorId)
            ->where('reservation_date', $reservationDate)
            ->where('reservation_time', $reservationTime)
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json(['error' => 'This time slot is already booked.'], 400);
        }

        // Create the reservation
        $reservation = Reservation::create([
            'doctor_id' => $doctorId,
            'reservation_date' => $reservationDate,
            'reservation_time' => $reservationTime,
            'user_id' => Auth::id(),
        ]);

        // Logowanie rezerwacji
        Log::info('Reservation created:', $reservation->toArray());

        return response()->json($reservation, 201);
    }

    /**
     * Delete an existing reservation.
     */
    public function destroy($id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

        // Only allow the user who created the reservation to cancel it
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reservation->delete();

        return response()->json(['message' => 'Reservation canceled successfully.']);
    }
}
