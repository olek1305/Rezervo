<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationController extends Controller
{
    // Pobierz rezerwacje zalogowanego użytkownika
    public function index(Request $request)
    {
        $reservations = Reservation::with('user')->get(); // Pobierz dane użytkownika dla każdej rezerwacji

        return response()->json($reservations->map(function ($reservation) {
            return [
                'reservation_date' => $reservation->reservation_date,
                'user_id' => $reservation->user_id,
                'user_name' => $reservation->user->name, // Zwróć nazwę użytkownika
            ];
        }));
    }

    // Dodaj nową rezerwację
    public function store(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date|unique:reservations,reservation_date,NULL,id,user_id,' . $request->user()->id,
        ]);

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'reservation_date' => $request->input('reservation_date'),
        ]);

        return response()->json($reservation, 201);
    }
}
