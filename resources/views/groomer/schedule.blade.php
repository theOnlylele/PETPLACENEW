<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groomer Schedule | PetPlace</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#eef4fd; --white:#fff; --border:#c8dff5; --text:#0f2137; --muted:#4a6080; --accent:#4f86c6; --accent-dark:#3d75b5; --pale:#deeaf8; --green:#21653f; --green-bg:#e4f4e8; --red:#994141; --red-bg:#fdeaea; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:24px; color:var(--text); font:15px/1.5 'DM Sans',sans-serif; background:var(--bg); background-image:radial-gradient(circle at 15% 15%,rgba(79,134,198,.1),transparent 50%),radial-gradient(circle at 85% 85%,rgba(79,134,198,.06),transparent 50%); }
        main { max-width:1100px; margin:0 auto; }
        header,.card { background:var(--white); border:1px solid var(--border); border-radius:16px; }
        header { padding:18px 22px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand { color:var(--accent); font-weight:800; font-size:20px; }
        h1 { margin:20px 0 4px; font-size:28px; } h2 { margin:0 0 8px; font-size:19px; } h3 { margin:0 0 12px; font-size:16px; }
        p { margin:5px 0; } .muted { color:var(--muted); }
        .button { display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--border); background:#fff; color:var(--muted); border-radius:9px; padding:9px 13px; font:600 14px 'DM Sans',sans-serif; cursor:pointer; text-decoration:none; }
        .primary { background:var(--accent); border-color:var(--accent); color:#fff; }
        .card { padding:20px; margin-top:18px; box-shadow:0 4px 18px rgba(79,134,198,.06); }
        .alert { margin:16px 0; padding:12px 15px; border-radius:10px; background:var(--green-bg); color:var(--green); border:1px solid #c9e8d1; }
        .errors { margin:16px 0; padding:12px 15px; border-radius:10px; background:var(--red-bg); color:var(--red); border:1px solid #f3caca; }
        .week-nav { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin:18px 0; }
        .calendar { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:10px; }
        .day { background:#f8fbff; border:1px solid #dce9f7; border-radius:12px; padding:11px; min-height:155px; }
        .day.today { border-color:var(--accent); box-shadow:inset 0 0 0 1px var(--accent); }
        .day-name { font-weight:700; color:#315f91; } .day-number { font-size:21px; font-weight:800; margin:3px 0 10px; }
        .event { background:#e7f0fb; border-left:3px solid var(--accent); border-radius:7px; padding:8px; margin:7px 0; font-size:12px; overflow-wrap:anywhere; }
        .event.completed { background:#e9e9f7; border-left-color:#6868a6; }
        .settings-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field label { display:block; font-weight:700; margin-bottom:7px; }
        input[type=time],input[type=date] { width:100%; max-width:100%; padding:11px; border:1px solid #cbd9e8; border-radius:9px; font:14px 'DM Sans',sans-serif; background:#fff; }
        .days { display:flex; flex-wrap:wrap; gap:9px; }
        .day-choice { display:flex; align-items:center; gap:7px; padding:9px 12px; border:1px solid #dce9f7; border-radius:9px; background:#f7faff; }
        .day-off-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
        .note { background:#f5f9ff; border:1px solid #dce9f7; padding:12px; border-radius:10px; margin:14px 0; }
        @media(max-width:850px) { .calendar { grid-template-columns:repeat(4,minmax(0,1fr)); } }
        @media(max-width:600px) { body { padding:14px; } header { padding:15px; } h1 { font-size:24px; } .calendar { grid-template-columns:repeat(2,minmax(0,1fr)); } .settings-grid { grid-template-columns:1fr; } .card { padding:14px; } }
    </style>
</head>
<body>
<main>
    <header>
        <div class="brand">PetPlace <span class="muted">· Groomer Portal</span></div>
        <div style="display:flex;gap:9px;flex-wrap:wrap;">
            <a href="{{ route('groomer.dashboard') }}" class="button">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="button">Log out</button>
            </form>
        </div>
    </header>

    <h1>My Schedule</h1>
    <p class="muted">Review your grooming appointments and set your regular working availability.</p>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            <strong>Please check the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="card">
        <h2>Weekly Appointment Calendar</h2>
        <p class="muted">{{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}</p>
        <div class="week-nav">
            <a class="button" href="{{ route('groomer.schedule', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}">← Previous Week</a>
            <a class="button primary" href="{{ route('groomer.schedule') }}">Today</a>
            <a class="button" href="{{ route('groomer.schedule', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}">Next Week →</a>
        </div>
        <div class="calendar">
            @foreach ($weekDays as $day)
                @php
                    $dayAppointments = $calendarAppointments->filter(fn ($appointment) => $appointment->appointment_at?->isSameDay($day));
                    $isDayOff = in_array($day->toDateString(), $daysOff, true);
                    $isWorkingDay = in_array($day->format('l'), $workingDays, true) && !$isDayOff;
                @endphp
                <div class="day {{ $day->isToday() ? 'today' : '' }}">
                    <div class="day-name">{{ $day->format('D') }}</div>
                    <div class="day-number">{{ $day->format('j') }}</div>
                    <p class="muted" style="font-size:11px;">{{ $isDayOff ? 'Day off' : ($isWorkingDay ? 'Working day' : 'Not scheduled') }}</p>
                    @forelse ($dayAppointments as $appointment)
                        <div class="event {{ $appointment->status === 'completed' ? 'completed' : '' }}">
                            <strong>{{ $appointment->appointment_at?->format('g:i A') }}</strong>
                            <div>{{ $appointment->pet?->pet_name ?? 'Pet' }}</div>
                            <div>{{ ucfirst($appointment->status) }}</div>
                        </div>
                    @empty
                        <p class="muted" style="font-size:11px;">No appointments</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </section>

    <section class="card">
        <h2>My Availability</h2>
        <p class="muted" style="margin-bottom:18px;">Choose the days and hours when you usually accept grooming appointments.</p>
        <form method="POST" action="{{ route('groomer.availability.update') }}">
            @csrf
            @method('PUT')
            <h3>Working Days</h3>
            <div class="days" style="margin-bottom:22px;">
                @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                    <label class="day-choice">
                        <input type="checkbox" name="working_days[]" value="{{ $day }}" @checked(in_array($day, old('working_days', $workingDays), true))>
                        {{ $day }}
                    </label>
                @endforeach
            </div>
            <div class="settings-grid" style="margin-bottom:22px;">
                <div class="field">
                    <label for="working_hours_start">Starting Time</label>
                    <input id="working_hours_start" type="time" name="working_hours_start" value="{{ old('working_hours_start', $groomer->working_hours_start ? substr($groomer->working_hours_start, 0, 5) : '') }}">
                </div>
                <div class="field">
                    <label for="working_hours_end">Ending Time</label>
                    <input id="working_hours_end" type="time" name="working_hours_end" value="{{ old('working_hours_end', $groomer->working_hours_end ? substr($groomer->working_hours_end, 0, 5) : '') }}">
                </div>
            </div>
            <h3>Specific Days Off</h3>
            <p class="muted" style="margin-bottom:12px;">Add dates when you will be unavailable, even if they are normally working days.</p>
            <div class="day-off-list">
                @foreach (old('days_off', $daysOff) as $dayOff)
                    <input type="date" name="days_off[]" min="{{ today()->toDateString() }}" value="{{ $dayOff }}">
                @endforeach
                @for ($i = 0; $i < 3; $i++)
                    <input type="date" name="days_off[]" min="{{ today()->toDateString() }}">
                @endfor
            </div>
            <div class="note muted">Saving availability updates your groomer profile. This page currently manages your schedule and calendar; appointment booking restrictions will be connected in the next step.</div>
            <button type="submit" class="button primary">Save Availability</button>
        </form>
    </section>
</main>
</body>
</html>
