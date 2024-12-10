<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Response;
use Inertia\ResponseFactory;


class DoctorAvailabilityController extends Controller
{
    public function index(): Response|ResponseFactory
    {
        $doctorId = Auth::id();

        $availabilities = Cache::remember("doctor_{$doctorId}_availabilities", 600, function () use ($doctorId) {
            return DoctorAvailability::where('doctor_id', $doctorId)
                ->where('available_date', '>=', now()->format('Y-m-d'))
                ->get();
        });

        return inertia('Doctor/Availability', [
            'availabilities' => $availabilities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->specialization === null) {
            return back()->with('error', 'Nie możesz ustawić terminu bez ustawionej specjalizacji.');
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

        Cache::forget("doctor_" . Auth::id() . "_availabilities");

        return back()->with('success', 'Availability added successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $availability = DoctorAvailability::findOrFail($id);

        if ($availability->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $availability->delete();

        Cache::forget("doctor_" . Auth::id() . "_availabilities");

        return back()->with('success', 'Availability removed successfully.');
    }
}
