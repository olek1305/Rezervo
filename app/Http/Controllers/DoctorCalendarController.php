<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DoctorCalendarController extends Controller
{
    /**
     * Generate a cache key for doctor data.
     */
    private function generateCacheKey(int $doctorId, string $type): string
    {
        return "doctor_{$doctorId}_{$type}";
    }

    /**
     * Index of doctor availabilities for the authenticated doctor.
     */
    public function index(): Response
    {
        if (Auth::user()->role !== 'doctor') {
            abort(403, 'Access denied.');
        }

        $doctorId = Auth::id();
        $cacheKey = $this->generateCacheKey($doctorId, 'availabilities');

        $availabilities = Cache::remember($cacheKey, 600, function () use ($doctorId) {
            return DoctorAvailability::where('doctor_id', $doctorId)->get();
        });

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

        $timeSlots = DoctorAvailability::where('doctor_id', $id)
            ->whereDate('available_date', '>=', today())
            ->get()
            ->mapWithKeys(function ($slot) {
                $date = (new \DateTime($slot->available_date))->format('Y-m-d');
                $startTime = new \DateTime($slot->start_time);
                $endTime = new \DateTime($slot->end_time);

                $slots = [];
                while ($startTime < $endTime) {
                    $slots[] = $startTime->format('H:i');
                    $startTime->modify('+30 minutes');
                }

                return [$date => $slots];
            });

        return Inertia::render('Client/Calendar', [
            'doctorId' => $id,
            'doctorName' => $doctor->name,
            'timeSlots' => $timeSlots, // Dodanie timeSlots do props
            'reservations' => Reservation::where('doctor_id', $id)
                ->whereDate('reservation_date', '>=', today())
                ->get()
                ->groupBy('reservation_date')
                ->map(fn ($res) => $res->pluck('reservation_time')),
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

        $doctorId = Auth::id();
        Cache::forget($this->generateCacheKey($doctorId, 'availabilities'));
        Cache::forget($this->generateCacheKey($doctorId, 'calendar'));

        return response()->json(['message' => 'Availability created successfully.'], 201);
    }
}

