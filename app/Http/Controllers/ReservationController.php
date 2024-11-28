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
     * Book a reservation for a specific doctor and time slot.
     */
    public function store(Request $request): JsonResponse
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
