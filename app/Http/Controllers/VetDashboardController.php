<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\VaccinationRecord;
use App\Models\Vet;
use App\Models\VisitRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VetDashboardController extends Controller
{
    private const VACCINE_OPTIONS = [
        'dog' => [
            'dog_rabies' => 'Anti-rabies',
            'dog_cdv' => 'Canine Distemper Virus (CDV)',
            'dog_adenovirus' => 'Canine Adenovirus Type 1 & 2',
            'dog_parvovirus' => 'Canine Parvovirus (CPV)',
            'dog_parainfluenza' => 'Canine Parainfluenza',
            'dog_leptospirosis' => 'Leptospirosis',
            'dog_lyme' => 'Lyme Disease',
            'dog_influenza' => 'Canine Influenza (Dog Flu)',
            'dog_coronavirus' => 'Canine Coronavirus',
            'dog_bordetella' => 'Bordetella bronchiseptica',
        ],
        'cat' => [
            'cat_fvrcp' => 'FVRCP (3-in-1 Combo Vaccine)',
            'cat_rhinotracheitis' => 'Feline Viral Rhinotracheitis',
            'cat_calicivirus' => 'Feline Calicivirus',
            'cat_panleukopenia' => 'Feline Panleukopenia',
            'cat_rabies' => 'Anti-rabies',
            'cat_felv' => 'Feline Leukemia Virus (FeLV)',
            'cat_fiv' => 'Feline Immunodeficiency Virus (FIV)',
            'cat_bordetella' => 'Bordetella bronchiseptica',
            'cat_fip' => 'Feline Infectious Peritonitis (FIP)',
        ],
    ];

    public function show(Request $request): View|RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $today = today();
        $weekValue = $request->validate(['week' => ['nullable', 'date']])['week'] ?? null;
        $calendarView = $request->input('calendar_view') === 'day' ? 'day' : 'week';
        $calendarDate = $weekValue ? Carbon::parse($weekValue) : $today->copy();
        $weekStart = $calendarView === 'day'
            ? $calendarDate->copy()->startOfDay()
            : $calendarDate->copy()->startOfWeek();
        $weekEnd = $calendarView === 'day'
            ? $calendarDate->copy()->endOfDay()
            : $calendarDate->copy()->endOfWeek();

        $appointments = Appointment::with(['pet', 'petOwner.user', 'vet'])
            ->where('service_type', '!=', 'Grooming')
            ->where(function ($query) use ($vet) {
                $query->where('vet_id', $vet->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned->whereNull('vet_id')->where('status', 'pending');
                    });
            });

        $pendingRequests = (clone $appointments)
            ->where('status', 'pending')
            ->orderBy('appointment_at')
            ->get();
        $calendarAppointments = Appointment::with(['pet', 'petOwner'])
            ->where('vet_id', $vet->id)
            ->where('status', 'confirmed')
            ->whereBetween('appointment_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->orderBy('appointment_at')
            ->get();
        $recentAppointments = Appointment::with(['pet', 'petOwner'])
            ->where('vet_id', $vet->id)
            ->where('service_type', '!=', 'Grooming')
            ->orderByDesc('appointment_at')
            ->limit(12)
            ->get();
        $todayAppointments = Appointment::where('vet_id', $vet->id)
            ->where('status', 'confirmed')
            ->whereDate('appointment_at', $today)
            ->count();
        $patientsSeenThisWeek = VisitRecord::where('vet_id', $vet->id)
            ->whereBetween('visit_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
            ->pluck('pet_id')
            ->merge(Appointment::where('vet_id', $vet->id)
                ->where('status', 'completed')
                ->whereBetween('appointment_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                ->pluck('pet_id'))
            ->unique()
            ->count();
        $vaccinationsDueSoon = VaccinationRecord::where(function ($query) use ($vet) {
            $query->where('vet_id', $vet->id)
                ->orWhereHas('pet.appointments', fn ($appointments) => $appointments->where('vet_id', $vet->id));
        })
            ->whereBetween('next_due_date', [$today, $today->copy()->addDays(30)])
            ->count();

        $patientsQuery = Pet::with(['petOwner.user'])
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) => $appointments
                    ->where('vet_id', $vet->id)
                    ->whereIn('status', ['confirmed', 'completed']))
                    ->orWhereHas('visitRecords', fn ($visits) => $visits->where('vet_id', $vet->id));
            });
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $searchTerms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            $patientsQuery->where(function ($query) use ($search, $searchTerms) {
                $query->where('pet_name', 'like', '%'.$search.'%')
                    ->orWhereHas('petOwner', function ($owner) use ($searchTerms) {
                        $owner->where(function ($names) use ($searchTerms) {
                            foreach ($searchTerms as $term) {
                                $names->where(function ($name) use ($term) {
                                    $name->where('first_name', 'like', '%'.$term.'%')
                                        ->orWhere('last_name', 'like', '%'.$term.'%');
                                });
                            }
                        });
                    });
            });
        }
        $patients = $patientsQuery->orderBy('pet_name')->paginate(10)->withQueryString();

        $selectedPatient = null;
        $selectedPatientVisits = collect();
        $selectedPatientVaccinations = collect();
        if ($request->filled('patient_id')) {
            $selectedPatient = (clone $patientsQuery)->whereKey($request->integer('patient_id'))->first();
            if ($selectedPatient) {
                $selectedPatientVisits = VisitRecord::where('vet_id', $vet->id)
                    ->where('pet_id', $selectedPatient->id)
                    ->orderByDesc('visit_date')
                    ->get();
                $selectedPatientVaccinations = $selectedPatient->vaccinationRecords()
                    ->with('veterinarian')
                    ->orderByDesc('date_given')
                    ->get();
            }
        }

        $notifications = Appointment::with(['pet', 'petOwner'])
            ->where(function ($query) use ($vet) {
                $query->where('vet_id', $vet->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned->whereNull('vet_id')->where('status', 'pending');
                    });
            })
            ->where(function ($query) use ($today) {
                $query->where('status', 'pending')
                    ->orWhere(function ($upcoming) use ($today) {
                        $upcoming->where('status', 'confirmed')
                            ->whereBetween('appointment_at', [$today->copy()->startOfDay(), $today->copy()->addDay()->endOfDay()]);
                    })
                    ->orWhere(function ($cancelled) use ($today) {
                        $cancelled->where('status', 'cancelled')
                            ->where('updated_at', '>=', $today->copy()->subDays(7)->startOfDay());
                    });
            })
            ->where('service_type', '!=', 'Grooming')
            ->orderBy('appointment_at')
            ->limit(8)
            ->get();

        return view('vet.dashboard', [
            'vet' => $vet,
            'todayAppointments' => $todayAppointments,
            'pendingCount' => $pendingRequests->count(),
            'patientsSeenThisWeek' => $patientsSeenThisWeek,
            'vaccinationsDueSoon' => $vaccinationsDueSoon,
            'pendingRequests' => $pendingRequests,
            'calendarAppointments' => $calendarAppointments,
            'recentAppointments' => $recentAppointments,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'selectedPatientVisits' => $selectedPatientVisits,
            'selectedPatientVaccinations' => $selectedPatientVaccinations,
            'patientVaccineOptions' => self::VACCINE_OPTIONS[strtolower($selectedPatient?->pet_type ?? '')] ?? [],
            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => $options)
                ->all(),
            'notifications' => $notifications,
            'weekDays' => $calendarView === 'day'
                ? collect([$calendarDate->copy()])
                : collect(range(0, 6))->map(fn (int $offset) => $weekStart->copy()->addDays($offset)),
            'calendarView' => $calendarView,
            'calendarDate' => $calendarDate,
            'search' => $request->input('search'),
        ]);
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        abort_unless($appointment->service_type !== 'Grooming', 404);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['accept', 'decline', 'reschedule', 'complete'])],
            'decision_reason' => ['required_if:action,decline,reschedule', 'nullable', 'string', 'max:1000'],
            'appointment_at' => ['required_if:action,reschedule', 'nullable', 'date', 'after:now'],
        ]);
        if ($validated['action'] === 'complete') {
            abort_unless($appointment->vet_id === $vet->id && $appointment->status === 'confirmed', 404);
        } else {
            abort_unless(
                $appointment->status === 'pending'
                && ($appointment->vet_id === null || $appointment->vet_id === $vet->id),
                404
            );
        }
        if ($validated['action'] === 'accept') {
            $this->assertAvailable($vet, $appointment->appointment_at);
        }

        if ($validated['action'] === 'accept') {
            $appointment->status = 'confirmed';
            $appointment->vet_id = $vet->id;
            $appointment->decision_reason = null;
        } elseif ($validated['action'] === 'decline') {
            $appointment->status = 'cancelled';
            $appointment->vet_id = $vet->id;
            $appointment->decision_reason = $validated['decision_reason'];
        } elseif ($validated['action'] === 'reschedule') {
            $this->assertAvailable($vet, Carbon::parse($validated['appointment_at']));
            $appointment->status = 'confirmed';
            $appointment->vet_id = $vet->id;
            $appointment->appointment_at = $validated['appointment_at'];
            $appointment->decision_reason = $validated['decision_reason'];
        } else {
            abort_unless($appointment->vet_id === $vet->id && $appointment->status === 'confirmed', 404);
            $appointment->status = 'completed';
        }

        $appointment->save();

        return back()->with('status', 'Appointment request updated.');
    }

    public function saveAvailability(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $validated = $request->validate([
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['required', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'working_hours_start' => ['required_with:working_days', 'nullable', 'date_format:H:i'],
            'working_hours_end' => ['required_with:working_days', 'nullable', 'date_format:H:i', 'after:working_hours_start'],
            'days_off' => ['nullable', 'array'],
            'days_off.*' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $vet->update([
            'working_days' => array_values(array_unique($validated['working_days'] ?? [])),
            'working_hours_start' => $validated['working_hours_start'] ?? null,
            'working_hours_end' => $validated['working_hours_end'] ?? null,
            'days_off' => array_values(array_unique($validated['days_off'] ?? [])),
        ]);

        return back()->with('status', 'Availability settings saved.');
    }

    public function storeVisit(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $validated = $request->validate([
            'pet_id' => ['required', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'treatment' => ['nullable', 'string', 'max:4000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'prescription_medicine' => ['nullable', 'string', 'max:255'],
            'prescription_dosage' => ['nullable', 'string', 'max:255'],
            'prescription_duration' => ['nullable', 'string', 'max:255'],
            'prescription_instructions' => ['nullable', 'string', 'max:1000'],
        ]);
        $pet = $this->patientForVet($vet, $validated['pet_id']);
        $appointment = null;
        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::where('id', $validated['appointment_id'])
                ->where('vet_id', $vet->id)
                ->where('pet_id', $pet->id)
                ->firstOrFail();
        }

        $prescription = array_filter([
            'medicine' => $validated['prescription_medicine'] ?? null,
            'dosage' => $validated['prescription_dosage'] ?? null,
            'duration' => $validated['prescription_duration'] ?? null,
            'instructions' => $validated['prescription_instructions'] ?? null,
        ]);
        VisitRecord::create([
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'appointment_id' => $appointment?->id,
            'visit_date' => $validated['visit_date'],
            'reason' => $validated['reason'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment' => $validated['treatment'] ?? null,
            'prescriptions' => $prescription ?: null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);
        if ($appointment && $appointment->status === 'confirmed') {
            $appointment->update(['status' => 'completed']);
        }

        return redirect()->route('vet.dashboard', ['patient_id' => $pet->id])
            ->with('status', 'Visit record saved.');
    }

    public function updatePatient(Request $request, Pet $pet): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $this->patientForVet($vet, $pet->id);
        $validated = $request->validate([
            'breed' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'gt:0', 'max:99999.99'],
            'allergies' => ['nullable', 'string', 'max:4000'],
        ]);
        $pet->update($validated);

        return redirect()->route('vet.dashboard', ['patient_id' => $pet->id])
            ->with('status', 'Patient profile updated.');
    }

    public function storeVaccination(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $validated = $request->validate([
            'pet_id' => ['required', 'integer'],
            'vaccine_key' => ['required', 'string', 'max:100'],
            'vaccine_name' => ['required_if:vaccine_key,other', 'nullable', 'string', 'max:100'],
            'category' => ['required', Rule::in(['Core', 'Non-core (Lifestyle)'])],
            'date_given' => ['required', 'date', 'before_or_equal:today'],
            'next_due_date' => ['required', 'date', 'after_or_equal:date_given'],
            'dose_number' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $pet = $this->patientForVet($vet, $validated['pet_id']);
        $validVaccineKeys = array_keys(self::VACCINE_OPTIONS[strtolower($pet->pet_type ?? '')] ?? []);
        if (in_array($validated['vaccine_key'], $validVaccineKeys, true)) {
            $vaccineKey = $validated['vaccine_key'];
        } elseif ($validated['vaccine_key'] === 'other') {
            $vaccineKey = trim($validated['vaccine_name']);
        } elseif (! $validVaccineKeys) {
            $vaccineKey = trim($validated['vaccine_key']);
        } else {
            throw ValidationException::withMessages([
                'vaccine_key' => 'Choose a listed vaccine or select Other.',
            ]);
        }

        $record = VaccinationRecord::create([
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'vaccine_key' => $vaccineKey,
            'category' => $validated['category'],
            'date_given' => $validated['date_given'],
            'next_due_date' => $validated['next_due_date'],
            'veterinarian_name' => $vet->first_name.' '.$vet->last_name,
            'dose_number' => $validated['dose_number'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'batch_number' => $validated['batch_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('vet.dashboard', ['patient_id' => $pet->id, 'certificate' => $record->id])
            ->with('status', 'Vaccination record saved.');
    }

    public function certificate(Request $request, VaccinationRecord $vaccinationRecord): View
    {
        $vet = $this->authenticatedVeterinarian($request);
        abort_unless($vaccinationRecord->vet_id === $vet->id, 404);

        return view('vet.vaccination-certificate', [
            'record' => $vaccinationRecord->load(['pet.petOwner', 'veterinarian']),
            'vaccineName' => self::VACCINE_OPTIONS[strtolower($vaccinationRecord->pet->pet_type ?? '')][$vaccinationRecord->vaccine_key] ?? $vaccinationRecord->vaccine_key,
        ]);
    }

    private function authenticatedVeterinarian(Request $request): Vet
    {
        abort_unless($request->user()?->role === 'vet', 403);

        return Vet::where('user_id', $request->user()->id)
            ->where('staff_type', 'veterinarian')
            ->firstOrFail();
    }

    private function patientForVet(Vet $vet, int $petId): Pet
    {
        return Pet::whereKey($petId)
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) => $appointments
                    ->where('vet_id', $vet->id)
                    ->whereIn('status', ['confirmed', 'completed']))
                    ->orWhereHas('visitRecords', fn ($visits) => $visits->where('vet_id', $vet->id));
            })
            ->firstOrFail();
    }

    private function assertAvailable(Vet $vet, Carbon $appointmentAt): void
    {
        $daysOff = $vet->days_off ?? [];
        $workingDays = $vet->working_days ?? [];
        if (in_array($appointmentAt->toDateString(), $daysOff, true)) {
            abort(422, 'The selected time is outside your saved availability.');
        }

        if (empty($workingDays) || ! $vet->working_hours_start || ! $vet->working_hours_end) {
            return;
        }

        if (! in_array($appointmentAt->format('l'), $workingDays, true)
            || $appointmentAt->format('H:i:s') < $vet->working_hours_start
            || $appointmentAt->format('H:i:s') >= $vet->working_hours_end) {
            abort(422, 'The selected time is outside your saved availability.');
        }
    }
}
