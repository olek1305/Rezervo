<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    /**
     * Generate a cache key for doctor data.
     */
    private function generateCacheKey(int $doctorId, string $type): string
    {
        return "doctor_{$doctorId}_{$type}";
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

        $doctor = User::findOrFail($request->doctor_id);

        $availabilitiesCacheKey = $this->generateCacheKey($request->doctor_id, 'availabilities');
        $calendarCacheKey = $this->generateCacheKey($request->doctor_id, 'calendar');

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
            ->exists();

        if ($isAlreadyBookedByUser) {
            return response()->json(['error' => 'You have already booked a reservation with this doctor on the same day.'], 422);
        }

        $reservation = Reservation::create([
            'doctor_id' => $request->doctor_id,
            'user_id' => Auth::id(),
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
        ]);

        Cache::forget($availabilitiesCacheKey);
        Cache::forget($calendarCacheKey);

        $reservationDetails = [
            'doctor_name' => $doctor->name,
            'specialization' => $doctor->specialization,
            'date' => $request->reservation_date,
            'time' => $request->reservation_time,
        ];

        Auth::user()->notify(new ReservationCreatedNotification($reservationDetails));

        return response()->json(['message' => 'Reservation created successfully.', 'reservation' => $reservation], 201);
    }

    /**
     * Delete an existing reservation.
     */
    public function destroy($id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $doctorId = $reservation->doctor_id;
        $availabilitiesCacheKey = $this->generateCacheKey($doctorId, 'availabilities');
        $calendarCacheKey = $this->generateCacheKey($doctorId, 'calendar');

        $reservation->delete();

        Cache::forget($availabilitiesCacheKey);
        Cache::forget($calendarCacheKey);

        return response()->json(['message' => 'Reservation canceled successfully.']);
    }
}
