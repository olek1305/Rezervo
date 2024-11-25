<?php

namespace App\Http\Controllers;

use App\Models\DoctorCalendar;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

        $doctorId = $request->doctor_id;
        $reservationDate = $request->reservation_date;

        // Check if the doctor is available on the selected date
        $availability = DoctorCalendar::where('doctor_id', $doctorId)
            ->where('available_date', $reservationDate)
            ->first();

        if (!$availability) {
            return response()->json(['error' => 'The doctor is not available on this date.'], 400);
        }

        // Check if the daily limit of 14 reservations has been reached
        $dailyCount = Reservation::where('doctor_id', $doctorId)
            ->where('reservation_date', $reservationDate)
            ->count();

        if ($dailyCount >= 14) {
            return response()->json(['error' => 'Reservation limit for this date has been reached.'], 400);
        }

        // Create the reservation
        $reservation = Reservation::create([
            'doctor_id' => $doctorId,
            'reservation_date' => $reservationDate,
            'user_id' => Auth::id(),
        ]);

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
