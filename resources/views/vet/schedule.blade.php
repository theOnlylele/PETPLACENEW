@extends('vet.layout')

@section('title', 'Schedule')
@section('page-title', 'Schedule')

@section('content')

@php
    $workingDays = is_array($vet->working_days)
        ? $vet->working_days
        : (json_decode($vet->working_days ?? '[]', true) ?: []);

    $daysOff = is_array($vet->days_off)
        ? $vet->days_off
        : (json_decode($vet->days_off ?? '[]', true) ?: []);

    $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];
@endphp

<div class="vet-page-heading">
    <h1>Schedule</h1>
    <p>Manage your appointments and working availability.</p>
</div>

{{-- WEEKLY CALENDAR --}}
<section class="vet-card" style="margin-bottom: 24px;">
    <div class="section-heading">
        <div>
            <h2>Appointment Calendar</h2>
            <p class="vet-muted">
                {{ $weekStart->format('M j') }}
                –
                {{ $weekEnd->format('M j, Y') }}
            </p>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <a href="{{ route('vet.schedule', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}"
           class="vet-btn vet-btn-secondary">
            ← Previous Week
        </a>

        <a href="{{ route('vet.schedule') }}"
           class="vet-btn vet-btn-primary">
            Today
        </a>

        <a href="{{ route('vet.schedule', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}"
           class="vet-btn vet-btn-secondary">
            Next Week →
        </a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:10px;">
        @foreach ($weekDays as $day)
            @php
                $dayAppointments = $calendarAppointments->filter(
                    fn ($appointment) =>
                        $appointment->appointment_at?->isSameDay($day)
                );
            @endphp

            <div style="background:#faf9ff; border:1px solid #eee8fa; border-radius:12px; padding:12px; min-height:150px;">
                <div style="font-weight:700; color:#49317d;">
                    {{ $day->format('D') }}
                </div>

                <div style="font-size:20px; font-weight:700; margin:5px 0 12px;">
                    {{ $day->format('j') }}
                </div>

                @forelse ($dayAppointments as $appointment)
                    <div style="background:#eee7ff; border-left:3px solid #8055d8; border-radius:6px; padding:8px; margin-bottom:8px; font-size:12px;">
                        <strong>
                            {{ $appointment->appointment_at?->format('g:i A') }}
                        </strong>
                        <div>
                            {{ $appointment->pet?->pet_name ?? 'Pet' }}
                        </div>
                        <small>
                            {{ ucfirst($appointment->status) }}
                        </small>
                    </div>
                @empty
                    <p style="font-size:12px; color:#999;">No appointments</p>
                @endforelse
            </div>
        @endforeach
    </div>
</section>

{{-- AVAILABILITY SETTINGS --}}
<section class="vet-card">
    <div class="section-heading">
        <div>
            <h2>My Availability</h2>
            <p class="vet-muted">
                Set the days and hours when you are available.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('vet.availability.update') }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom:22px;">
            <h3 style="margin-bottom:12px;">Working Days</h3>

            <div style="display:flex; flex-wrap:wrap; gap:12px;">
                @foreach ($days as $day)
                    <label style="display:flex; align-items:center; gap:7px; padding:10px 14px; background:#f7f4ff; border-radius:9px;">
                        <input
                            type="checkbox"
                            name="working_days[]"
                            value="{{ $day }}"
                            @checked(in_array($day, $workingDays))
                        >
                        {{ $day }}
                    </label>
                @endforeach
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin-bottom:22px;">
            <div>
                <label for="working_hours_start">
                    **Starting Time**
                </label>

                <input
                    id="working_hours_start"
                    type="time"
                    name="working_hours_start"
                    value="{{ $vet->working_hours_start ? substr($vet->working_hours_start, 0, 5) : '' }}"
                    style="display:block; width:100%; padding:12px; margin-top:8px; border:1px solid #ddd; border-radius:8px;"
                >
            </div>

            <div>
                <label for="working_hours_end">
                    **Ending Time**
                </label>

                <input
                    id="working_hours_end"
                    type="time"
                    name="working_hours_end"
                    value="{{ $vet->working_hours_end ? substr($vet->working_hours_end, 0, 5) : '' }}"
                    style="display:block; width:100%; padding:12px; margin-top:8px; border:1px solid #ddd; border-radius:8px;"
                >
            </div>
        </div>

        <div style="margin-bottom:22px;">
            <h3 style="margin-bottom:12px;">Days Off</h3>

            @forelse ($daysOff as $dayOff)
                <div style="margin-bottom:10px;">
                    <input
                        type="date"
                        name="days_off[]"
                        value="{{ $dayOff }}"
                        style="padding:10px; border:1px solid #ddd; border-radius:8px;"
                    >
                </div>
            @empty
                <p class="vet-muted">No days off scheduled.</p>
            @endforelse

            <input
                type="date"
                name="days_off[]"
                min="{{ today()->toDateString() }}"
                style="padding:10px; border:1px solid #ddd; border-radius:8px; margin-top:8px;"
            >
        </div>

        <button type="submit" class="vet-btn vet-btn-primary">
            Save Availability
        </button>
    </form>
</section>

@endsection