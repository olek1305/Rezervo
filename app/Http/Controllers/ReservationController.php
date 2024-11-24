<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservations = Reservation::with('user')->get();

        return response()->json($reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'reservation_date' => $reservation->reservation_date,
                'user_id' => $reservation->user_id,
                'user_name' => $reservation->user->name,
            ];
        }));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->toDateString(),
                Rule::unique('reservations')
                    ->where('user_id', $request->user()->id),
            ],
        ]);

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'reservation_date' => $request->input('reservation_date'),
        ]);

        return response()->json($reservation, 201);
    }

    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['error' => 'Rezerwacja nie istnieje.'], 404);
        }

        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nie masz uprawnień do tej rezerwacji.'], 403);
        }

        $reservation->delete();

        return response()->json(['message' => 'Rezerwacja została anulowana.'], 200);
    }


}
