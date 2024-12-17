<?php

namespace App\Http\Controllers;

use App\Models\DoctorAvailability;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationsDeletedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class AdminDoctorController extends Controller
{
    public function index(Request $request): Response|ResponseFactory
    {
        $search = $request->input('search', '');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(50);

        return inertia('Admin/Doctors/Index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function assignRole(User $user): RedirectResponse
    {
        if ($user->role === 'doctor') {
            return back()->with('error', 'Ten użytkownik już posiada rolę doctor.');
        }

        $user->update(['role' => 'doctor']);
        return back()->with('success', 'Użytkownik otrzymał rolę doctor.');
    }

    public function removeRole(User $user): RedirectResponse
    {
        if ($user->role !== 'doctor') {
            return back()->with('error', 'Ten użytkownik nie posiada roli doctor.');
        }

        $doctorId = $user->id;
        $affectedUsers = Reservation::where('doctor_id', $doctorId)
            ->pluck('user_id')
            ->unique();

        DoctorAvailability::where('doctor_id', $doctorId)->delete();
        Reservation::where('doctor_id', $doctorId)->delete();

        $user->update([
            'role' => 'user',
            'specialization' => null,
        ]);

        $doctorName = $user->name;
        $specialization = $user->specialization;

        User::whereIn('id', $affectedUsers)->each(function ($notifiableUser) use ($doctorName, $specialization) {
            $notifiableUser->notify(new ReservationsDeletedNotification($doctorName, $specialization));
        });

        auth()->user()->notify(new ReservationsDeletedNotification($doctorName, $specialization));

        return back()->with('success', 'Rola Doctor została usunięta, a powiązane dane zostały wyczyszczone.');
    }
}
