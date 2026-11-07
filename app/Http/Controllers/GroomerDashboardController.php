<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroomerDashboardController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->user()?->role === 'vet', 403);

        $groomer = Vet::where('user_id', $request->user()->id)
            ->where('staff_type', 'groomer')
            ->firstOrFail();
        $appointments = Appointment::with(['pet', 'petOwner'])
            ->where('service_type', 'Grooming')
            ->where(function ($query) use ($groomer) {
                $query->where('groomer_id', $groomer->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned->whereNull('groomer_id')->where('status', 'pending');
                    });
            })
            ->whereIn('status', ['pending', 'confirmed', 'completed', 'cancelled'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'confirmed' THEN 1 ELSE 2 END")
            ->orderBy('appointment_at')
            ->limit(50)
            ->get();

        return view('groomer.dashboard', [
            'groomer' => $groomer,
            'appointments' => $appointments,
            'todayCount' => $appointments->filter(fn ($appointment) => $appointment->status === 'confirmed'
                && $appointment->appointment_at?->isToday())->count(),
            'pendingCount' => $appointments->where('status', 'pending')->count(),
        ]);
    }
}
