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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function show(Request $request): View|RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $today = today();

        $weekValue = $request->validate([
            'week' => ['nullable', 'date'],
        ])['week'] ?? null;

        $calendarView = $request->input('calendar_view') === 'day'
            ? 'day'
            : 'week';

        $calendarDate = $weekValue
            ? Carbon::parse($weekValue)
            : $today->copy();

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
                        $unassigned->whereNull('vet_id')
                            ->where('status', 'pending');
                    });
            });

        $pendingRequests = (clone $appointments)
            ->where('status', 'pending')
            ->orderBy('appointment_at')
            ->get();

        $calendarAppointments = (clone $appointments)
            ->whereBetween('appointment_at', [$weekStart, $weekEnd])
            ->orderBy('appointment_at')
            ->get();

        $upcomingAppointments = Appointment::with(['pet', 'petOwner'])
            ->where('vet_id', $vet->id)
            ->where('status', 'confirmed')
            ->where('appointment_at', '>=', now())
            ->orderBy('appointment_at')
            ->limit(5)
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
            ->whereBetween('visit_date', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
            ])
            ->pluck('pet_id')
            ->merge(
                Appointment::where('vet_id', $vet->id)
                    ->where('status', 'completed')
                    ->whereBetween('appointment_at', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek(),
                    ])
                    ->pluck('pet_id')
            )
            ->unique()
            ->count();

        $vaccinationsDueSoon = VaccinationRecord::where(function ($query) use ($vet) {
            $query->where('vet_id', $vet->id)
                ->orWhereHas('pet.appointments', fn ($appointments) =>
                    $appointments->where('vet_id', $vet->id)
                );
        })
            ->whereBetween('next_due_date', [
                $today,
                $today->copy()->addDays(30),
            ])
            ->count();

        $patientsQuery = Pet::with(['petOwner.user'])
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) =>
                    $appointments
                        ->where('vet_id', $vet->id)
                        ->whereIn('status', ['confirmed', 'completed'])
                )->orWhereHas('visitRecords', fn ($visits) =>
                    $visits->where('vet_id', $vet->id)
                )->orWhereHas('vaccinationRecords', fn ($vaccinations) =>
                    $vaccinations->where('vet_id', $vet->id)
                );
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

        $patients = $patientsQuery
            ->orderBy('pet_name')
            ->paginate(10)
            ->withQueryString();

        $selectedPatient = null;
        $selectedPatientVisits = collect();
        $selectedPatientVaccinations = collect();

        if ($request->filled('patient_id')) {
            $selectedPatient = (clone $patientsQuery)
                ->whereKey($request->integer('patient_id'))
                ->first();

            if ($selectedPatient) {
                $selectedPatientVisits = VisitRecord::where('vet_id', $vet->id)
                    ->where('pet_id', $selectedPatient->id)
                    ->orderByDesc('visit_date')
                    ->get();

                $selectedPatientVaccinations = $selectedPatient
                    ->vaccinationRecords()
                    ->where('vet_id', $vet->id)
                    ->with('veterinarian')
                    ->orderByDesc('date_given')
                    ->get();
            }
        }

        $notifications = Appointment::with(['pet', 'petOwner'])
            ->where(function ($query) use ($vet) {
                $query->where('vet_id', $vet->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned->whereNull('vet_id')
                            ->where('status', 'pending');
                    });
            })
            ->where(function ($query) use ($today) {
                $query->where('status', 'pending')
                    ->orWhere(function ($upcoming) use ($today) {
                        $upcoming->where('status', 'confirmed')
                            ->whereBetween('appointment_at', [
                                $today->copy()->startOfDay(),
                                $today->copy()->addDay()->endOfDay(),
                            ]);
                    })
                    ->orWhere(function ($cancelled) use ($today) {
                        $cancelled->where('status', 'cancelled')
                            ->where(
                                'updated_at',
                                '>=',
                                $today->copy()->subDays(7)->startOfDay()
                            );
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
            'upcomingAppointments' => $upcomingAppointments,
            'recentAppointments' => $recentAppointments,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'selectedPatientVisits' => $selectedPatientVisits,
            'selectedPatientVaccinations' => $selectedPatientVaccinations,
            'patientVaccineOptions' => self::VACCINE_OPTIONS[
                strtolower($selectedPatient?->pet_type ?? '')
            ] ?? [],
            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => $options)
                ->all(),
            'notifications' => $notifications,
            'weekDays' => $calendarView === 'day'
                ? collect([$calendarDate->copy()])
                : collect(range(0, 6))->map(
                    fn (int $offset) => $weekStart->copy()->addDays($offset)
                ),
            'calendarView' => $calendarView,
            'calendarDate' => $calendarDate,
            'search' => $request->input('search'),
            'activePage' => 'dashboard',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | APPOINTMENTS PAGE
    |--------------------------------------------------------------------------
    */

    public function appointments(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        $appointmentsQuery = Appointment::with([
            'pet',
            'petOwner.user',
            'vet',
        ])
            ->where('service_type', '!=', 'Grooming')
            ->where(function ($query) use ($vet) {
                $query->where('vet_id', $vet->id)
                    ->orWhere(function ($unassigned) {
                        $unassigned->whereNull('vet_id')
                            ->where('status', 'pending');
                    });
            });

        $pendingRequests = (clone $appointmentsQuery)
            ->where('status', 'pending')
            ->orderBy('appointment_at')
            ->get();

        $selectedStatus = $request->input('status');

        $appointments = (clone $appointmentsQuery)
            ->when(
                in_array($selectedStatus, [
                    'pending',
                    'confirmed',
                    'completed',
                    'cancelled',
                ], true),
                fn ($query) => $query->where('status', $selectedStatus)
            )
            ->orderByDesc('appointment_at')
            ->paginate(10)
            ->withQueryString();

        return view('vet.appointments', [
            'vet' => $vet,
            'pendingRequests' => $pendingRequests,
            'appointments' => $appointments,
            'selectedStatus' => $selectedStatus,
            'vaccineOptionsByType' => self::VACCINE_OPTIONS,
            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => $options)
                ->all(),
            'activePage' => 'appointments',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SCHEDULE PAGE
    |--------------------------------------------------------------------------
    */

    public function schedule(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        $weekValue = $request->validate([
            'week' => ['nullable', 'date'],
        ])['week'] ?? null;

        $calendarDate = $weekValue
            ? Carbon::parse($weekValue)
            : today();

        $weekStart = $calendarDate->copy()->startOfWeek();
        $weekEnd = $calendarDate->copy()->endOfWeek();

        $calendarAppointments = Appointment::with([
            'pet',
            'petOwner',
        ])
            ->where('vet_id', $vet->id)
            ->where('service_type', '!=', 'Grooming')
            ->whereBetween('appointment_at', [
                $weekStart,
                $weekEnd,
            ])
            ->orderBy('appointment_at')
            ->get();

        $weekDays = collect(range(0, 6))->map(
            fn (int $offset) => $weekStart->copy()->addDays($offset)
        );

        return view('vet.schedule', [
            'vet' => $vet,
            'calendarAppointments' => $calendarAppointments,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekDays' => $weekDays,
            'activePage' => 'schedule',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VET PROFILE
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        return view('vet.profile', [
            'vet' => $vet,
            'user' => $request->user(),
            'activePage' => 'profile',
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'specialty' => [
                'nullable',
                Rule::in(['dog', 'cat', 'rabbit', 'fish', 'bird', 'reptile']),
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);

        $user->update([
            'name' => $firstName.' '.$lastName,
            'email' => $validated['email'],
        ]);

        $photoPath = $vet->profile_photo;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')
                ->store('vet-profiles', 'public');

            if ($vet->profile_photo) {
                Storage::disk('public')->delete($vet->profile_photo);
            }

            $photoPath = $newPhotoPath;
        }

        $vet->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $validated['phone'] ?? null,
            'clinic_name' => $validated['clinic_name'] ?? null,
            'specialty' => $validated['specialty'] ?? null,
            'profile_photo' => $photoPath,
        ]);

        return redirect()
            ->route('vet.profile')
            ->with('status', 'Your profile has been updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENTS PAGE
    |--------------------------------------------------------------------------
    */

    public function patients(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        $patientsQuery = Pet::with(['petOwner.user'])
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) =>
                    $appointments
                        ->where('vet_id', $vet->id)
                        ->whereIn('status', ['confirmed', 'completed'])
                )->orWhereHas('visitRecords', fn ($visits) =>
                    $visits->where('vet_id', $vet->id)
                )->orWhereHas('vaccinationRecords', fn ($vaccinations) =>
                    $vaccinations->where('vet_id', $vet->id)
                );
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

        $patients = $patientsQuery
            ->orderBy('pet_name')
            ->paginate(10)
            ->withQueryString();

        $selectedPatient = null;
        $selectedPatientVisits = collect();
        $selectedPatientVaccinations = collect();

        if ($request->filled('patient_id')) {
            $selectedPatient = (clone $patientsQuery)
                ->whereKey($request->integer('patient_id'))
                ->first();

            if ($selectedPatient) {
                $selectedPatientVisits = VisitRecord::where('vet_id', $vet->id)
                    ->where('pet_id', $selectedPatient->id)
                    ->orderByDesc('visit_date')
                    ->get();

                $selectedPatientVaccinations = $selectedPatient
                    ->vaccinationRecords()
                    ->where('vet_id', $vet->id)
                    ->with('veterinarian')
                    ->orderByDesc('date_given')
                    ->get();
            }
        }

        return view('vet.patients', [
            'vet' => $vet,
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'selectedPatientVisits' => $selectedPatientVisits,
            'selectedPatientVaccinations' => $selectedPatientVaccinations,
            'patientVaccineOptions' => self::VACCINE_OPTIONS[
                strtolower($selectedPatient?->pet_type ?? '')
            ] ?? [],
            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => $options)
                ->all(),
            'search' => $request->input('search'),
            'activePage' => 'patients',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VACCINATIONS PAGE
    |--------------------------------------------------------------------------
    */

    public function vaccinations(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        $today = today();

        $search = trim((string) $request->input('search', ''));
        $filter = $request->input('filter', 'all');

        $vaccinationsQuery = VaccinationRecord::with([
            'pet.petOwner.user',
            'veterinarian',
        ])
            ->where('vet_id', $vet->id);

        if ($search !== '') {
            $vaccinationsQuery->where(function ($query) use ($search) {
                $query->where('vaccine_key', 'like', '%'.$search.'%')
                    ->orWhere('brand', 'like', '%'.$search.'%')
                    ->orWhere('batch_number', 'like', '%'.$search.'%')
                    ->orWhereHas('pet', function ($pet) use ($search) {
                        $pet->where('pet_name', 'like', '%'.$search.'%')
                            ->orWhereHas('petOwner', function ($owner) use ($search) {
                                $owner->where('first_name', 'like', '%'.$search.'%')
                                    ->orWhere('last_name', 'like', '%'.$search.'%');
                            });
                    });
            });
        }

        if ($filter === 'overdue') {
            $vaccinationsQuery->whereDate('next_due_date', '<', $today);
        } elseif ($filter === 'due_soon') {
            $vaccinationsQuery->whereBetween('next_due_date', [
                $today,
                $today->copy()->addDays(30),
            ]);
        } elseif ($filter === 'upcoming') {
            $vaccinationsQuery->whereDate(
                'next_due_date',
                '>',
                $today->copy()->addDays(30)
            );
        }

        $vaccinations = $vaccinationsQuery
            ->orderBy('next_due_date')
            ->paginate(10)
            ->withQueryString();

        $patients = Pet::with(['petOwner.user'])
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) =>
                    $appointments
                        ->where('vet_id', $vet->id)
                        ->whereIn('status', ['confirmed', 'completed'])
                )->orWhereHas('visitRecords', fn ($visits) =>
                    $visits->where('vet_id', $vet->id)
                )->orWhereHas('vaccinationRecords', fn ($vaccinations) =>
                    $vaccinations->where('vet_id', $vet->id)
                );
            })
            ->orderBy('pet_name')
            ->get();

        $vaccinationStats = [
            'total' => VaccinationRecord::where('vet_id', $vet->id)->count(),

            'overdue' => VaccinationRecord::where('vet_id', $vet->id)
                ->whereDate('next_due_date', '<', $today)
                ->count(),

            'due_soon' => VaccinationRecord::where('vet_id', $vet->id)
                ->whereBetween('next_due_date', [
                    $today,
                    $today->copy()->addDays(30),
                ])
                ->count(),

            'upcoming' => VaccinationRecord::where('vet_id', $vet->id)
                ->whereDate(
                    'next_due_date',
                    '>',
                    $today->copy()->addDays(30)
                )
                ->count(),
        ];

        return view('vet.vaccinations', [
            'vet' => $vet,
            'vaccinations' => $vaccinations,
            'patients' => $patients,
            'vaccinationStats' => $vaccinationStats,
            'search' => $search,
            'filter' => $filter,
            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => $options)
                ->all(),
            'activePage' => 'vaccinations',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENT HISTORY PAGE
    |--------------------------------------------------------------------------
    */

    public function history(Request $request): View
    {
        $vet = $this->authenticatedVeterinarian($request);

        $search = trim((string) $request->input('search', ''));

        $visitsQuery = VisitRecord::with([
            'pet.petOwner.user',
            'appointment',
        ])->where('vet_id', $vet->id);

        $vaccinationsQuery = VaccinationRecord::with([
            'pet.petOwner.user',
            'veterinarian',
        ])->where('vet_id', $vet->id);

        if ($search !== '') {
            $visitsQuery->whereHas('pet', function ($pet) use ($search) {
                $pet->where('pet_name', 'like', '%'.$search.'%')
                    ->orWhereHas('petOwner', function ($owner) use ($search) {
                        $owner->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%');
                    });
            });

            $vaccinationsQuery->whereHas('pet', function ($pet) use ($search) {
                $pet->where('pet_name', 'like', '%'.$search.'%')
                    ->orWhereHas('petOwner', function ($owner) use ($search) {
                        $owner->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%');
                    });
            });
        }

        $visits = $visitsQuery
            ->orderByDesc('visit_date')
            ->paginate(10, ['*'], 'visits_page')
            ->withQueryString();

        $vaccinations = $vaccinationsQuery
            ->orderByDesc('date_given')
            ->paginate(10, ['*'], 'vaccinations_page')
            ->withQueryString();

        return view('vet.history', [
            'vet' => $vet,
            'visits' => $visits,
            'vaccinations' => $vaccinations,
            'search' => $search,
            'activePage' => 'history',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE APPOINTMENT
    |--------------------------------------------------------------------------
    */

    public function updateAppointment(
        Request $request,
        Appointment $appointment
    ): RedirectResponse {
        $vet = $this->authenticatedVeterinarian($request);

        abort_unless($appointment->service_type !== 'Grooming', 404);

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in(['accept', 'decline', 'reschedule']),
            ],
            'decision_reason' => [
                'required_if:action,decline,reschedule',
                'nullable',
                'string',
                'max:1000',
            ],
            'appointment_at' => [
                'required_if:action,reschedule',
                'nullable',
                'date',
                'after:now',
            ],
        ]);

        abort_unless(
            $appointment->status === 'pending'
            && (
                $appointment->vet_id === null
                || $appointment->vet_id === $vet->id
            ),
            404
        );

        if ($validated['action'] === 'accept') {
            $this->assertAvailable($vet, $appointment->appointment_at);

            $appointment->status = 'confirmed';
            $appointment->vet_id = $vet->id;
            $appointment->decision_reason = null;

        } elseif ($validated['action'] === 'decline') {
            $appointment->status = 'cancelled';
            $appointment->vet_id = $vet->id;
            $appointment->decision_reason = $validated['decision_reason'];

        } elseif ($validated['action'] === 'reschedule') {
            $this->assertAvailable(
                $vet,
                Carbon::parse($validated['appointment_at'])
            );

            $appointment->status = 'confirmed';
            $appointment->vet_id = $vet->id;
            $appointment->appointment_at = $validated['appointment_at'];
            $appointment->decision_reason = $validated['decision_reason'];
        }

        $appointment->save();

        return back()->with('status', 'Appointment request updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function saveAvailability(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);

        $validated = $request->validate([
            'working_days' => ['nullable', 'array'],
            'working_days.*' => [
                'required',
                Rule::in([
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
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
                'required',
                'date',
                'after_or_equal:today',
            ],
        ]);

        $vet->update([
            'working_days' => array_values(
                array_unique($validated['working_days'] ?? [])
            ),
            'working_hours_start' => $validated['working_hours_start'] ?? null,
            'working_hours_end' => $validated['working_hours_end'] ?? null,
            'days_off' => array_values(
                array_unique($validated['days_off'] ?? [])
            ),
        ]);

        return back()->with('status', 'Availability settings saved.');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE VISIT
    |--------------------------------------------------------------------------
    */

    public function storeVisit(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
            'visit_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'reason' => ['nullable', 'string', 'max:2000'],
            'diagnosis' => ['nullable', 'string', 'max:4000'],
            'treatment' => ['nullable', 'string', 'max:4000'],
            'follow_up_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
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
                ->where('status', 'confirmed')
                ->firstOrFail();

            abort_if(
                str_contains(strtolower($appointment->service_type ?? ''), 'vaccin'),
                422,
                'Use the vaccination form for vaccination appointments.'
            );
        }

        $prescription = array_filter([
            'medicine' => $validated['prescription_medicine'] ?? null,
            'dosage' => $validated['prescription_dosage'] ?? null,
            'duration' => $validated['prescription_duration'] ?? null,
            'instructions' => $validated['prescription_instructions'] ?? null,
        ]);

        DB::transaction(function () use (
            $pet,
            $vet,
            $appointment,
            $validated,
            $prescription
        ) {
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

            if ($appointment) {
                $appointment->update(['status' => 'completed']);
            }
        });

        return redirect()
            ->route('vet.history')
            ->with('status', 'Visit record saved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PATIENT
    |--------------------------------------------------------------------------
    */

    public function updatePatient(
        Request $request,
        Pet $pet
    ): RedirectResponse {
        $vet = $this->authenticatedVeterinarian($request);

        $this->patientForVet($vet, $pet->id);

        $validated = $request->validate([
            'breed' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'gt:0', 'max:99999.99'],
            'allergies' => ['nullable', 'string', 'max:4000'],
        ]);

        $pet->update($validated);

        return redirect()
            ->route('vet.dashboard', ['patient_id' => $pet->id])
            ->with('status', 'Patient profile updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE VACCINATION
    |--------------------------------------------------------------------------
    */

    public function storeVaccination(Request $request): RedirectResponse
    {
        $vet = $this->authenticatedVeterinarian($request);

        $validated = $request->validate([
            'pet_id' => ['required', 'integer'],
            'appointment_id' => ['nullable', 'integer'],
            'vaccine_key' => ['required', 'string', 'max:100'],
            'vaccine_name' => [
                'required_if:vaccine_key,other',
                'nullable',
                'string',
                'max:100',
            ],
            'category' => [
                'required',
                Rule::in([
                    'Core',
                    'Non-core (Lifestyle)',
                    'core',
                    'non-core',
                ]),
            ],
            'date_given' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'next_due_date' => [
                'nullable',
                'date',
                'after_or_equal:date_given',
            ],
            'dose_number' => ['nullable', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $pet = $this->patientForVet($vet, $validated['pet_id']);

        $appointment = null;

        if (! empty($validated['appointment_id'])) {
            $appointment = Appointment::where('id', $validated['appointment_id'])
                ->where('vet_id', $vet->id)
                ->where('pet_id', $pet->id)
                ->where('status', 'confirmed')
                ->firstOrFail();

            abort_unless(
                str_contains(strtolower($appointment->service_type ?? ''), 'vaccin'),
                422,
                'This appointment is not a vaccination appointment.'
            );
        }

        $validVaccineKeys = array_keys(
            self::VACCINE_OPTIONS[strtolower($pet->pet_type ?? '')] ?? []
        );

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

        $category = in_array(
            strtolower($validated['category']),
            ['non-core', 'non-core (lifestyle)'],
            true
        ) ? 'Non-core (Lifestyle)' : 'Core';

        $record = DB::transaction(function () use (
            $pet,
            $vet,
            $appointment,
            $validated,
            $vaccineKey,
            $category
        ) {
            $record = VaccinationRecord::create([
                'pet_id' => $pet->id,
                'vet_id' => $vet->id,
                'vaccine_key' => $vaccineKey,
                'category' => $category,
                'date_given' => $validated['date_given'],
                'next_due_date' => $validated['next_due_date'] ?? null,
                'veterinarian_name' => $vet->first_name.' '.$vet->last_name,
                'dose_number' => $validated['dose_number'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'batch_number' => $validated['batch_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'completed']);
            }

            return $record;
        });

        if ($appointment) {
            return redirect()
                ->route('vet.history')
                ->with('status', 'Vaccination saved and appointment completed.');
        }

        return redirect()
            ->route('vet.dashboard', [
                'patient_id' => $pet->id,
                'certificate' => $record->id,
            ])
            ->with('status', 'Vaccination record saved.');
    }

    /*
    |--------------------------------------------------------------------------
    | VACCINATION CERTIFICATE
    |--------------------------------------------------------------------------
    */

    public function certificate(
        Request $request,
        VaccinationRecord $vaccinationRecord
    ): View {
        $vet = $this->authenticatedVeterinarian($request);

        abort_unless($vaccinationRecord->vet_id === $vet->id, 404);

        return view('vet.vaccination-certificate', [
            'record' => $vaccinationRecord->load([
                'pet.petOwner',
                'veterinarian',
            ]),
            'vaccineName' => self::VACCINE_OPTIONS[
                strtolower($vaccinationRecord->pet->pet_type ?? '')
            ][$vaccinationRecord->vaccine_key]
                ?? $vaccinationRecord->vaccine_key,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED VETERINARIAN
    |--------------------------------------------------------------------------
    */

    private function authenticatedVeterinarian(Request $request): Vet
    {
        abort_unless($request->user()?->role === 'vet', 403);

        return Vet::where('user_id', $request->user()->id)
            ->where('staff_type', 'veterinarian')
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | PATIENT ACCESS CHECK
    |--------------------------------------------------------------------------
    */

    private function patientForVet(Vet $vet, int $petId): Pet
    {
        return Pet::whereKey($petId)
            ->where(function ($query) use ($vet) {
                $query->whereHas('appointments', fn ($appointments) =>
                    $appointments
                        ->where('vet_id', $vet->id)
                        ->whereIn('status', ['confirmed', 'completed'])
                )->orWhereHas('visitRecords', fn ($visits) =>
                    $visits->where('vet_id', $vet->id)
                )->orWhereHas('vaccinationRecords', fn ($vaccinations) =>
                    $vaccinations->where('vet_id', $vet->id)
                );
            })
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | AVAILABILITY CHECK
    |--------------------------------------------------------------------------
    */

    private function assertAvailable(
        Vet $vet,
        Carbon $appointmentAt
    ): void {
        $daysOff = $vet->days_off ?? [];
        $workingDays = $vet->working_days ?? [];

        if (in_array($appointmentAt->toDateString(), $daysOff, true)) {
            abort(422, 'The selected time is outside your saved availability.');
        }

        if (
            empty($workingDays)
            || ! $vet->working_hours_start
            || ! $vet->working_hours_end
        ) {
            return;
        }

        if (
            ! in_array($appointmentAt->format('l'), $workingDays, true)
            || $appointmentAt->format('H:i:s') < $vet->working_hours_start
            || $appointmentAt->format('H:i:s') >= $vet->working_hours_end
        ) {
            abort(422, 'The selected time is outside your saved availability.');
        }
    }
}
