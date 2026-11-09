<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroomerDashboardController extends Controller
{
    /**
     * Display grooming requests and the groomer's appointments.
     */
    public function show(Request $request): View
    {
        $groomer = $this->authenticatedGroomer($request);

        $appointments = Appointment::with(['pet', 'petOwner'])
            ->where('service_type', 'Grooming')
            ->where(function ($query) use ($groomer) {
                $query->where('groomer_id', $groomer->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned
                            ->whereNull('groomer_id')
                            ->where('status', 'pending');
                    });
            })
            ->whereIn('status', ['pending', 'confirmed', 'completed', 'cancelled'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'confirmed' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END")
            ->orderBy('appointment_at')
            ->limit(100)
            ->get();

        return view('groomer.dashboard', [
            'groomer' => $groomer,
            'appointments' => $appointments,
            'todayCount' => $appointments->filter(fn ($appointment) =>
                $appointment->status === 'confirmed'
                && $appointment->appointment_at?->isToday()
            )->count(),
            'pendingCount' => $appointments->where('status', 'pending')->count(),
        ]);
    }

    /**
     * Display the groomer's weekly calendar and availability settings.
     */
    public function schedule(Request $request): View
    {
        $groomer = $this->authenticatedGroomer($request);

        $weekValue = $request->validate([
            'week' => ['nullable', 'date'],
        ])['week'] ?? null;

        $calendarDate = $weekValue ? Carbon::parse($weekValue) : today();
        $weekStart = $calendarDate->copy()->startOfWeek();
        $weekEnd = $calendarDate->copy()->endOfWeek();

        $calendarAppointments = Appointment::with(['pet', 'petOwner'])
            ->where('service_type', 'Grooming')
            ->where('groomer_id', $groomer->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('appointment_at', [$weekStart, $weekEnd])
            ->orderBy('appointment_at')
            ->get();

        $weekDays = collect(range(0, 6))->map(
            fn (int $offset) => $weekStart->copy()->addDays($offset)
        );

        return view('groomer.schedule', [
            'groomer' => $groomer,
            'calendarAppointments' => $calendarAppointments,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekDays' => $weekDays,
            'workingDays' => $groomer->working_days ?? [],
            'daysOff' => $groomer->days_off ?? [],
        ]);
    }

    /**
     * Save the groomer's recurring working days, hours, and specific days off.
     */
    public function saveAvailability(Request $request): RedirectResponse
    {
        $groomer = $this->authenticatedGroomer($request);

        $validated = $request->validate([
            'working_days' => ['nullable', 'array'],
            'working_days.*' => [
                'required',
                Rule::in([
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday',
                    'Friday', 'Saturday', 'Sunday',
                ]),
            ],
            'working_hours_start' => [
                'required_with:working_days',
                'nullable',
                'date_format:H:i',
            ],
            'working_hours_end' => [
                'required_with:working_days',
                'nullable',
                'date_format:H:i',
                'after:working_hours_start',
            ],
            'days_off' => ['nullable', 'array'],
            'days_off.*' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ]);

        $daysOff = array_values(array_unique(array_filter(
            $validated['days_off'] ?? [],
            fn ($date) => filled($date)
        )));

        $groomer->update([
            'working_days' => array_values(array_unique($validated['working_days'] ?? [])),
            'working_hours_start' => $validated['working_hours_start'] ?? null,
            'working_hours_end' => $validated['working_hours_end'] ?? null,
            'days_off' => $daysOff,
        ]);

        return redirect()
            ->route('groomer.schedule')
            ->with('status', 'Your grooming availability has been saved.');
    }

    /**
     * Accept, decline, or complete a grooming appointment.
     */
    public function updateAppointment(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $groomer = $this->authenticatedGroomer($request);

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in(['accept', 'decline', 'complete']),
            ],
            'decision_reason' => [
                'required_if:action,decline',
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use ($appointment, $groomer, $validated) {
            // Lock the row so two groomers cannot accept the same request at once.
            $lockedAppointment = Appointment::whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedAppointment->service_type === 'Grooming', 404);

            if (in_array($validated['action'], ['accept', 'decline'], true)) {
                abort_unless(
                    $lockedAppointment->status === 'pending'
                    && (
                        $lockedAppointment->groomer_id === null
                        || (int) $lockedAppointment->groomer_id === (int) $groomer->id
                    ),
                    404
                );

                // Claim the request for this groomer before changing its status.
                $lockedAppointment->groomer_id = $groomer->id;

                if ($validated['action'] === 'accept') {
                    $lockedAppointment->status = 'confirmed';
                    $lockedAppointment->decision_reason = null;
                } else {
                    $lockedAppointment->status = 'cancelled';
                    $lockedAppointment->decision_reason = $validated['decision_reason'];
                }
            } else {
                // Only the groomer assigned to a confirmed appointment can complete it.
                abort_unless(
                    $lockedAppointment->status === 'confirmed'
                    && (int) $lockedAppointment->groomer_id === (int) $groomer->id,
                    404
                );

                $lockedAppointment->status = 'completed';
            }

            $lockedAppointment->save();
        });

        $message = match ($validated['action']) {
            'accept' => 'Grooming appointment accepted successfully.',
            'decline' => 'Grooming request declined.',
            'complete' => 'Grooming appointment marked as completed.',
        };

        return redirect()
            ->route('groomer.dashboard')
            ->with('status', $message);
    }

    /**
     * Make sure the signed-in staff account is an approved groomer.
     */
    private function authenticatedGroomer(Request $request): Vet
    {
        abort_unless($request->user()?->role === 'vet', 403);

        return Vet::where('user_id', $request->user()->id)
            ->where('staff_type', 'groomer')
            ->where('approval_status', 'approved')
            ->firstOrFail();
    }
}
