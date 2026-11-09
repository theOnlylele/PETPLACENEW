<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veterinarian Dashboard | PetPlace</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0f2137; --muted:#4a6080; --green:#4f86c6; --pale:#deeaf8; --line:#c8dff5; --white:#fff; --red:#b54747; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); background:#eef4fd; background-image:radial-gradient(circle at 15% 15%,rgba(79,134,198,.1) 0%,transparent 50%),radial-gradient(circle at 85% 85%,rgba(79,134,198,.06) 0%,transparent 50%); font:15px/1.5 'DM Sans',sans-serif; }
        button,input,select,textarea { font:inherit; }
        a { color:var(--green); }
        .layout { min-height:100vh; display:grid; grid-template-columns:235px minmax(0,1fr); }
        aside { background:#fff; border-right:1px solid var(--line); padding:28px 18px; }
        .brand { margin:0 12px 30px; font-size:22px; color:var(--green); font-weight:800; }
        .nav a { display:block; padding:11px 13px; margin:4px 0; border-radius:9px; color:var(--muted); text-decoration:none; font-weight:600; }
        .nav a:hover { background:var(--pale); color:var(--green); }
        .side-bottom { margin:30px 12px 0; color:var(--muted); font-size:13px; }
        main { min-width:0; padding:30px clamp(18px,4vw,54px) 55px; max-width:1500px; width:100%; margin:auto; }
        header { display:flex; justify-content:space-between; align-items:center; gap:18px; margin-bottom:25px; }
        h1 { margin:0; font-size:28px; }
        h2 { margin:0 0 16px; font-size:20px; }
        h3 { margin:0 0 7px; font-size:16px; }
        p { margin:5px 0; }
        .muted,.subtle { color:var(--muted); }
        .logout { border:0; background:#fff; border-radius:8px; padding:10px 14px; cursor:pointer; color:var(--ink); }
        .cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin:20px 0 28px; }
        .metric,.panel,.request-card,.patient-card { background:#fff; border:1px solid var(--line); border-radius:14px; }
        .metric { padding:18px; }
        .metric strong { display:block; font-size:28px; color:var(--green); margin-bottom:3px; }
        .metric span { color:var(--muted); font-size:13px; }
        .panel { padding:22px; margin:18px 0; scroll-margin-top:15px; }
        .section-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
        .request-list { display:grid; gap:12px; }
        .request-card { padding:16px; display:grid; grid-template-columns:minmax(210px,1fr) minmax(220px,1.3fr); gap:16px; }
        .request-meta { display:flex; flex-wrap:wrap; gap:8px; margin-top:9px; }
        .pill { display:inline-block; padding:4px 9px; border-radius:20px; font-size:12px; font-weight:700; background:var(--pale); color:var(--accent-dark,#3d75b5); }
        .pill.pending { color:#8a6500; background:#fff4ce; }
        .pill.confirmed { color:#21653f; background:#e4f4e8; }
        .pill.cancelled { color:#994141; background:#fdeaea; }
        .actions { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-top:12px; }
        .btn { display:inline-block; background:var(--green); color:#fff; border:0; border-radius:8px; padding:9px 13px; text-decoration:none; font-weight:700; cursor:pointer; }
        .btn.secondary { background:var(--pale); color:#3d75b5; }
        .btn.danger { background:#fff0f0; color:var(--red); }
        .btn.small { padding:7px 10px; font-size:13px; }
        .inline { display:inline; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .field { display:flex; flex-direction:column; gap:5px; margin-bottom:10px; }
        .field.full { grid-column:1/-1; }
        label { font-size:13px; font-weight:700; }
        input,select,textarea { width:100%; border:1px solid var(--line); border-radius:8px; padding:9px 10px; background:#f5f9ff; color:var(--ink); }
        input:focus,select:focus,textarea:focus { outline:none; border-color:var(--green); box-shadow:0 0 0 3px rgba(79,134,198,.1); }
        textarea { min-height:75px; resize:vertical; }
        .check-grid { display:flex; flex-wrap:wrap; gap:8px 15px; }
        .check-grid label { display:flex; align-items:center; gap:6px; font-weight:500; }
        .check-grid input { width:auto; }
        .error { color:#b42318; font-size:12px; }
        .notice { padding:12px 15px; background:#e8f1fc; color:#2f5e91; border:1px solid var(--line); border-radius:9px; margin-bottom:16px; }
        .split { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr); gap:18px; align-items:start; }
        .calendar-day { border-top:1px solid var(--line); padding:12px 0; }
        .calendar-day:first-of-type { border-top:0; }
        .event { background:#f5f9ff; border-left:3px solid var(--green); border-radius:5px; padding:8px 10px; margin:7px 0; }
        .patient-list { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:10px; margin:14px 0; }
        .patient-card { padding:12px; text-decoration:none; color:var(--ink); }
        .patient-card:hover,.patient-card.selected { border-color:var(--green); background:var(--pale); }
        .record { padding:11px 0; border-top:1px solid var(--line); }
        .record:first-child { border-top:0; }
        .pagination { margin-top:14px; }
        .pagination nav { display:flex; gap:8px; }
        @media(max-width:900px) { .layout{grid-template-columns:1fr} aside{padding:12px 16px;border-right:0;border-bottom:1px solid var(--line)} .brand{margin:0 0 9px}.nav{display:flex;overflow:auto;gap:4px}.nav a{white-space:nowrap;margin:0}.side-bottom{display:none}.cards{grid-template-columns:repeat(2,minmax(0,1fr))}.split{grid-template-columns:1fr} }
        @media(max-width:600px) { main{padding:20px 14px 40px}header{align-items:flex-start}h1{font-size:23px}.cards{gap:8px}.metric{padding:13px}.metric strong{font-size:23px}.request-card{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.panel{padding:16px} }
        @media print { aside,header,.no-print { display:none!important } body{background:#fff}.layout{display:block}main{padding:0}.panel{border:0} }
    </style>
</head>
<body>
<div class="layout">
    <aside>
        <div class="brand">PetPlace <span class="muted">| Vet</span></div>
        <nav class="nav" aria-label="Veterinarian navigation">
            <a href="#overview">Dashboard</a>
            <a href="#requests">Appointments</a>
            <a href="#schedule">Schedule</a>
            <a href="#patients">Patients</a>
            <a href="#vaccinations">Vaccinations</a>
            <a href="#profile">Profile</a>
        </nav>
        <div class="side-bottom">Signed in as<br><strong>Dr. {{ $vet->first_name }} {{ $vet->last_name }}</strong></div>
    </aside>
    <main>
        <header>
            <div>
                <h1 id="overview">Good day, Dr. {{ $vet->first_name }}</h1>
                <p class="muted">{{ now()->format('l, F j, Y') }} · Veterinarian dashboard</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="no-print">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </header>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice" style="background:#fff0f0;color:#8f2929">
                <strong>Please check the form:</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="cards" aria-label="Today's overview">
            <div class="metric"><strong>{{ $todayAppointments }}</strong><span>Appointments today</span></div>
            <div class="metric"><strong>{{ $pendingCount }}</strong><span>Pending requests</span></div>
            <div class="metric"><strong>{{ $patientsSeenThisWeek }}</strong><span>Patients seen this week</span></div>
            <div class="metric"><strong>{{ $vaccinationsDueSoon }}</strong><span>Vaccinations due in 30 days</span></div>
        </section>

        <section class="panel" id="requests">
            <div class="section-head">
                <div><h2>Appointment requests</h2><p class="muted">Veterinary requests assigned to you or awaiting a veterinarian.</p></div>
            </div>
            @forelse ($pendingRequests as $appointment)
                <article class="request-card">
                    <div>
                        <h3>{{ $appointment->pet?->pet_name ?? 'Pet' }} <span class="pill pending">Pending</span></h3>
                        <p>{{ ucfirst($appointment->pet?->pet_type ?? 'Pet') }} · {{ $appointment->service_type }}</p>
                        <p class="muted">Owner: {{ $appointment->petOwner?->first_name }} {{ $appointment->petOwner?->last_name }}
                            @if ($appointment->petOwner?->phone) · {{ $appointment->petOwner->phone }} @endif</p>
                        <p><strong>Requested:</strong> {{ $appointment->appointment_at?->format('M j, Y g:i A') }}</p>
                        @if ($appointment->reason)<p><strong>Client notes:</strong> {{ $appointment->reason }}</p>@endif
                        @if ($appointment->vet_id === null)<p class="muted">Not assigned to a veterinarian yet.</p>@endif
                    </div>
                    <div>
                        <form method="POST" action="{{ route('vet.appointments.update', $appointment) }}" class="no-print">
                            @csrf @method('PATCH')
                            <div class="actions">
                                <button class="btn small" name="action" value="accept">Accept</button>
                                <button class="btn danger small" name="action" value="decline" onclick="return confirm('Decline this request? Add a reason first.')">Decline</button>
                            </div>
                            <div class="field" style="margin-top:10px">
                                <label for="reason-{{ $appointment->id }}">Reason if declining or rescheduling</label>
                                <input id="reason-{{ $appointment->id }}" name="decision_reason" maxlength="1000" placeholder="Required for decline or reschedule">
                            </div>
                            <div class="actions">
                                <label for="reschedule-{{ $appointment->id }}">New date/time</label>
                                <input id="reschedule-{{ $appointment->id }}" type="datetime-local" name="appointment_at">
                                <button class="btn secondary small" name="action" value="reschedule">Reschedule</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <p class="muted">There are no pending veterinary requests.</p>
            @endforelse
        </section>

        <div class="split">
            <section class="panel" id="schedule">
                <div class="section-head">
                    <div><h2>Schedule</h2><p class="muted">{{ $calendarDate->format('M j, Y') }} @if($calendarView === 'week') – {{ $weekEnd->format('M j, Y') }} @endif</p></div>
                    <div class="actions no-print">
                        <a class="btn small {{ $calendarView === 'day' ? '' : 'secondary' }}" href="{{ route('vet.dashboard', ['calendar_view' => 'week', 'week' => $calendarDate->toDateString()]) }}">Week</a>
                        <a class="btn small {{ $calendarView === 'day' ? 'secondary' : '' }}" href="{{ route('vet.dashboard', ['calendar_view' => 'day', 'week' => today()->toDateString()]) }}">Today</a>
                        <form method="GET" action="{{ route('vet.dashboard') }}" class="actions">
                            <input type="hidden" name="calendar_view" value="{{ $calendarView }}">
                            <input type="date" name="week" value="{{ $calendarDate->toDateString() }}" aria-label="Choose calendar date">
                            <button class="btn secondary small" type="submit">Go</button>
                        </form>
                    </div>
                </div>
                @forelse ($weekDays as $day)
                    <div class="calendar-day">
                        <strong>{{ $day->format('l, M j') }}</strong>
                        @php($dayAppointments = $calendarAppointments->filter(fn ($item) => $item->appointment_at?->isSameDay($day)))
                        @forelse ($dayAppointments as $appointment)
                            <div class="event">
                                <strong>{{ $appointment->appointment_at->format('g:i A') }} · {{ $appointment->pet?->pet_name }}</strong>
                                <div>{{ $appointment->service_type }} · {{ $appointment->petOwner?->first_name }} {{ $appointment->petOwner?->last_name }}</div>
                                <form method="POST" action="{{ route('vet.appointments.update', $appointment) }}" class="actions no-print">
                                    @csrf @method('PATCH')
                                    <button class="btn secondary small" name="action" value="complete">Mark completed</button>
                                </form>
                            </div>
                        @empty
                            <p class="muted">No confirmed appointments.</p>
                        @endforelse
                    </div>
                @empty
                    <p class="muted">No schedule found.</p>
                @endforelse
            </section>

            <section class="panel" id="profile">
                <h2>Availability</h2>
                <p class="muted">Set the days and hours you accept appointments. Day-off dates are excluded.</p>
                <form method="POST" action="{{ route('vet.availability.update') }}">
                    @csrf @method('PUT')
                    <div class="field">
                        <label>Working days</label>
                        <div class="check-grid">
                            @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <label><input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, old('working_days', $vet->working_days ?? []), true))>{{ substr($day, 0, 3) }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="field"><label for="start-time">Start</label><input id="start-time" type="time" name="working_hours_start" value="{{ old('working_hours_start', $vet->working_hours_start) }}"></div>
                        <div class="field"><label for="end-time">End</label><input id="end-time" type="time" name="working_hours_end" value="{{ old('working_hours_end', $vet->working_hours_end) }}"></div>
                        <div class="field full"><label for="day-off">Add a day off</label><input id="day-off" type="date" name="days_off[]" min="{{ today()->toDateString() }}"></div>
                    </div>
                    @if ($vet->days_off)
                        <p class="muted">Saved days off: {{ collect($vet->days_off)->join(', ') }}</p>
                        @foreach ($vet->days_off as $dayOff)<input type="hidden" name="days_off[]" value="{{ $dayOff }}">@endforeach
                    @endif
                    <button class="btn" type="submit">Save availability</button>
                </form>
            </section>
        </div>

        <section class="panel" aria-label="Recent appointment statuses">
            <h2>Recent appointment activity</h2>
            @forelse ($recentAppointments as $appointment)
                @php($statusClass = in_array($appointment->status, ['pending', 'confirmed', 'cancelled'], true) ? $appointment->status : '')
                <div class="record">
                    <strong>{{ $appointment->pet?->pet_name ?? 'Pet' }}</strong>
                    <span class="pill {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                    <p>{{ $appointment->service_type }} · {{ $appointment->appointment_at?->format('M j, Y g:i A') }} · {{ $appointment->petOwner?->first_name }} {{ $appointment->petOwner?->last_name }}</p>
                    @if($appointment->decision_reason)<p class="muted">Decision note: {{ $appointment->decision_reason }}</p>@endif
                </div>
            @empty
                <p class="muted">No appointment history yet.</p>
            @endforelse
        </section>

        <section class="panel" id="patients">
            <div class="section-head">
                <div><h2>Patient records</h2><p class="muted">Patients with an appointment or visit record connected to your account.</p></div>
                <form method="GET" action="{{ route('vet.dashboard') }}" class="actions no-print">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Pet or owner name" aria-label="Search patients">
                    <button class="btn small" type="submit">Search</button>
                </form>
            </div>
            <div class="patient-list">
                @forelse ($patients as $patient)
                    <a class="patient-card {{ $selectedPatient?->id === $patient->id ? 'selected' : '' }}" href="{{ route('vet.dashboard', ['patient_id' => $patient->id, 'search' => $search]) }}">
                        <strong>{{ $patient->pet_name }}</strong>
                        <div class="muted">{{ ucfirst($patient->pet_type) }} · {{ $patient->petOwner?->first_name }} {{ $patient->petOwner?->last_name }}</div>
                    </a>
                @empty
                    <p class="muted">No patients found for your appointments yet.</p>
                @endforelse
            </div>
            <div class="pagination">{{ $patients->links() }}</div>

            @if ($selectedPatient)
                <div class="record">
                    <div class="section-head">
                        <div><h3>{{ $selectedPatient->pet_name }}'s profile</h3><p class="muted">{{ ucfirst($selectedPatient->pet_type) }} · {{ $selectedPatient->breed ?: 'Breed not recorded' }}</p></div>
                        <span class="pill">{{ $selectedPatient->pet_birthdate ? $selectedPatient->pet_birthdate->age.' years old' : 'Birthdate not recorded' }}</span>
                    </div>
                    <p><strong>Owner:</strong> {{ $selectedPatient->petOwner?->first_name }} {{ $selectedPatient->petOwner?->last_name }}
                        · {{ $selectedPatient->petOwner?->phone ?: 'No phone recorded' }}
                        · {{ $selectedPatient->petOwner?->user?->email }}</p>
                    <form method="POST" action="{{ route('vet.patients.update', $selectedPatient) }}" class="no-print" style="margin-top:14px">
                        @csrf @method('PUT')
                        <div class="form-grid">
                            <div class="field"><label for="breed">Breed</label><input id="breed" name="breed" value="{{ old('breed', $selectedPatient->breed) }}"></div>
                            <div class="field"><label for="weight">Weight (kg)</label><input id="weight" type="number" step="0.01" min="0.01" name="weight" value="{{ old('weight', $selectedPatient->weight) }}"></div>
                            <div class="field full"><label for="allergies">Allergies / health notes</label><textarea id="allergies" name="allergies">{{ old('allergies', $selectedPatient->allergies) }}</textarea></div>
                        </div>
                        <button class="btn secondary" type="submit">Update patient profile</button>
                    </form>

                    <h3 style="margin-top:24px">Record a visit</h3>
                    <form method="POST" action="{{ route('vet.visits.store') }}" class="no-print">
                        @csrf
                        <input type="hidden" name="pet_id" value="{{ $selectedPatient->id }}">
                        <div class="form-grid">
                            <div class="field"><label for="visit-date">Visit date</label><input id="visit-date" type="date" name="visit_date" max="{{ today()->toDateString() }}" value="{{ old('visit_date', today()->toDateString()) }}" required></div>
                            <div class="field"><label for="appointment-id">Appointment (optional)</label>
                                <select id="appointment-id" name="appointment_id"><option value="">Not linked</option>
                                    @foreach ($selectedPatient->appointments()->where('vet_id', $vet->id)->orderByDesc('appointment_at')->limit(20)->get() as $patientAppointment)
                                        <option value="{{ $patientAppointment->id }}">{{ $patientAppointment->appointment_at->format('M j, Y') }} · {{ $patientAppointment->status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field full"><label for="visit-reason">Reason for visit</label><textarea id="visit-reason" name="reason">{{ old('reason') }}</textarea></div>
                            <div class="field full"><label for="diagnosis">Diagnosis</label><textarea id="diagnosis" name="diagnosis">{{ old('diagnosis') }}</textarea></div>
                            <div class="field full"><label for="treatment">Treatment and notes</label><textarea id="treatment" name="treatment">{{ old('treatment') }}</textarea></div>
                            <div class="field"><label for="follow-up">Follow-up date</label><input id="follow-up" type="date" name="follow_up_date" min="{{ today()->toDateString() }}"></div>
                            <div class="field"><label for="medicine">Prescription medicine</label><input id="medicine" name="prescription_medicine"></div>
                            <div class="field"><label for="dosage">Dosage</label><input id="dosage" name="prescription_dosage"></div>
                            <div class="field"><label for="duration">Duration</label><input id="duration" name="prescription_duration"></div>
                            <div class="field full"><label for="instructions">Prescription instructions</label><textarea id="instructions" name="prescription_instructions"></textarea></div>
                        </div>
                        <button class="btn" type="submit">Save visit record</button>
                    </form>
                    <h3 style="margin-top:24px">Visit history</h3>
                    @forelse ($selectedPatientVisits as $visit)
                        <div class="record">
                            <strong>{{ $visit->visit_date->format('M j, Y') }}</strong>
                            @if($visit->reason)<p><strong>Reason:</strong> {{ $visit->reason }}</p>@endif
                            @if($visit->diagnosis)<p><strong>Diagnosis:</strong> {{ $visit->diagnosis }}</p>@endif
                            @if($visit->treatment)<p><strong>Treatment:</strong> {{ $visit->treatment }}</p>@endif
                            @if($visit->follow_up_date)<p><strong>Follow-up:</strong> {{ $visit->follow_up_date->format('M j, Y') }}</p>@endif
                            @if($visit->prescriptions)<p><strong>Prescription:</strong> {{ collect($visit->prescriptions)->filter()->join(' · ') }}</p>@endif
                        </div>
                    @empty
                        <p class="muted">No visit records yet.</p>
                    @endforelse
                </div>

                <div class="record" id="vaccinations">
                    <h3>Vaccination history</h3>
                    @forelse ($selectedPatientVaccinations as $record)
                        <div class="record">
                            <strong>{{ $vaccineLabels[$record->vaccine_key] ?? $record->vaccine_key }}</strong>
                            <span class="pill">{{ $record->category }}</span>
                            @if (!$record->next_due_date)
                                <span class="pill">Not yet due</span>
                            @elseif ($record->next_due_date->isBefore(today()))
                                <span class="pill cancelled">Overdue</span>
                            @elseif ($record->next_due_date->lessThanOrEqualTo(today()->addDays(30)))
                                <span class="pill pending">Due soon</span>
                            @else
                                <span class="pill confirmed">Up to date</span>
                            @endif
                            <p>Given {{ $record->date_given?->format('M j, Y') }} · Next due {{ $record->next_due_date?->format('M j, Y') ?: 'Not set' }}
                                @if($record->dose_number) · Dose {{ $record->dose_number }} @endif</p>
                            @if($record->brand || $record->batch_number)<p class="muted">{{ $record->brand }} @if($record->batch_number) · Lot {{ $record->batch_number }} @endif</p>@endif
                            @if($record->notes)<p>{{ $record->notes }}</p>@endif
                            @if($record->vet_id === $vet->id)
                                <a class="btn secondary small no-print" href="{{ route('vet.vaccinations.certificate', $record) }}" target="_blank" rel="noopener">View certificate</a>
                            @endif
                        </div>
                    @empty
                        <p class="muted">No vaccinations recorded.</p>
                    @endforelse

                    <h3 style="margin-top:22px">Log a vaccination</h3>
                    <form method="POST" action="{{ route('vet.vaccinations.store') }}" class="no-print">
                        @csrf
                        <input type="hidden" name="pet_id" value="{{ $selectedPatient->id }}">
                        <div class="form-grid">
                            <div class="field full"><label for="vaccine-key">Vaccine</label>
                                @if ($patientVaccineOptions)
                                    <select id="vaccine-key" name="vaccine_key" required>
                                        <option value="">Choose vaccine</option>
                                        @foreach($patientVaccineOptions as $key => $name)<option value="{{ $key }}">{{ $name }}</option>@endforeach
                                        <option value="other">Other / custom vaccine</option>
                                    </select>
                                    <input id="custom-vaccine-name" name="vaccine_name" maxlength="100" placeholder="Enter vaccine name" style="display:none;margin-top:8px">
                                @else
                                    <input id="vaccine-key" name="vaccine_key" maxlength="100" placeholder="Enter vaccine name" required>
                                @endif
                            </div>
                            <div class="field"><label for="vaccine-category">Category</label><select id="vaccine-category" name="category" required><option>Core</option><option>Non-core (Lifestyle)</option></select></div>
                            <div class="field"><label for="dose-number">Dose number</label><input id="dose-number" name="dose_number" placeholder="1st, 2nd, booster"></div>
                            <div class="field"><label for="date-given">Date given</label><input id="date-given" type="date" name="date_given" max="{{ today()->toDateString() }}" required></div>
                            <div class="field"><label for="next-due">Next due date</label><input id="next-due" type="date" name="next_due_date" required></div>
                            <div class="field"><label for="brand">Brand</label><input id="brand" name="brand"></div>
                            <div class="field"><label for="batch-number">Batch / lot number</label><input id="batch-number" name="batch_number"></div>
                            <div class="field full"><label for="vaccine-notes">Notes</label><textarea id="vaccine-notes" name="notes"></textarea></div>
                        </div>
                        <button class="btn" type="submit">Save vaccination record</button>
                    </form>
                </div>
            @endif
        </section>

        <section class="panel" aria-label="Notifications">
            <h2>Notifications</h2>
            @forelse ($notifications as $notification)
                <div class="record">
                    <strong>{{ $notification->status === 'pending' ? 'New appointment request' : ($notification->status === 'cancelled' ? 'Appointment cancelled or declined' : 'Upcoming appointment') }}</strong>
                    <p>{{ $notification->pet?->pet_name }} · {{ $notification->appointment_at?->format('M j, Y g:i A') }} · {{ $notification->service_type }}</p>
                </div>
            @empty
                <p class="muted">No new requests or appointments coming up.</p>
            @endforelse
        </section>
    </main>
</div>
<script>
    const vaccineSelect = document.getElementById('vaccine-key');
    const customVaccineName = document.getElementById('custom-vaccine-name');
    if (vaccineSelect && customVaccineName) {
        const syncCustomVaccine = () => {
            const isCustom = vaccineSelect.value === 'other';
            customVaccineName.style.display = isCustom ? 'block' : 'none';
            customVaccineName.required = isCustom;
        };
        vaccineSelect.addEventListener('change', syncCustomVaccine);
        syncCustomVaccine();
    }
</script>
</body>
</html>
