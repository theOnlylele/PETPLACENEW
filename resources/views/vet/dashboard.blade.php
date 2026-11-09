
@extends('vet.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@php
    $upcomingAppointments = $calendarAppointments
        ->filter(fn ($appointment) =>
            $appointment->appointment_at &&
            $appointment->appointment_at->isFuture()
        )
        ->take(5);
@endphp

<div class="vet-page-heading">
    <h1>Good day, Dr. {{ $vet->first_name }}!</h1>
    <p>Here's an overview of your veterinary practice.</p>
</div>

{{-- DASHBOARD STATISTICS --}}
<div class="dashboard-stats">

    <div class="stat-card">
        <div class="stat-icon purple">▣</div>
        <div>
            <span>Today's Appointments</span>
            <h2>{{ $todayAppointments }}</h2>
            <small>Confirmed appointments</small>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">◷</div>
        <div>
            <span>Pending Requests</span>
            <h2>{{ $pendingCount }}</h2>
            <small>Awaiting your decision</small>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">♡</div>
        <div>
            <span>Patients Seen</span>
            <h2>{{ $patientsSeenThisWeek }}</h2>
            <small>This week</small>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">✚</div>
        <div>
            <span>Vaccinations Due</span>
            <h2>{{ $vaccinationsDueSoon }}</h2>
            <small>Within the next 30 days</small>
        </div>
    </div>

</div>

{{-- UPCOMING APPOINTMENTS AND QUICK ACTIONS --}}
<div class="dashboard-grid">

    <section class="vet-card">
        <div class="section-heading">
            <div>
                <h2>Upcoming Appointments</h2>
                <p class="vet-muted">Your next confirmed appointments</p>
            </div>

            <a href="{{ url('/vet/appointments') }}"
               class="vet-btn vet-btn-secondary">
                View All
            </a>
        </div>

        @forelse ($upcomingAppointments as $appointment)

            <div class="appointment-item">

                <div class="appointment-date">
                    <strong>{{ $appointment->appointment_at->format('d') }}</strong>
                    <span>{{ $appointment->appointment_at->format('M') }}</span>
                </div>

                <div class="appointment-info">
                    <strong>{{ $appointment->pet?->pet_name ?? 'Pet' }}</strong>

                    <p>
                        {{ $appointment->service_type }}
                        ·
                        {{ $appointment->petOwner?->first_name }}
                        {{ $appointment->petOwner?->last_name }}
                    </p>

                    <small>
                        {{ $appointment->appointment_at->format('g:i A') }}
                    </small>
                </div>

                <span class="status-label confirmed">Confirmed</span>

            </div>

        @empty

            <div class="empty-state">
                <div class="empty-icon">▣</div>
                <strong>No upcoming appointments</strong>
                <p>Your upcoming confirmed appointments will appear here.</p>
            </div>

        @endforelse
    </section>

    <section class="vet-card">

        <div class="section-heading">
            <div>
                <h2>Quick Actions</h2>
                <p class="vet-muted">Manage your veterinary work</p>
            </div>
        </div>

        <div class="quick-actions">

            <a href="{{ url('/vet/appointments') }}" class="quick-action">
                <span class="quick-icon">▣</span>
                <span>
                    <strong>Appointments</strong>
                    <small>Review and manage requests</small>
                </span>
                <span class="arrow">›</span>
            </a>

            <a href="{{ url('/vet/schedule') }}" class="quick-action">
                <span class="quick-icon">▦</span>
                <span>
                    <strong>My Schedule</strong>
                    <small>View your working schedule</small>
                </span>
                <span class="arrow">›</span>
            </a>

            <a href="{{ url('/vet/patients') }}" class="quick-action">
                <span class="quick-icon">♡</span>
                <span>
                    <strong>Patient Records</strong>
                    <small>View patient information</small>
                </span>
                <span class="arrow">›</span>
            </a>

            <a href="{{ url('/vet/vaccinations') }}" class="quick-action">
                <span class="quick-icon">✚</span>
                <span>
                    <strong>Vaccinations</strong>
                    <small>Manage vaccination records</small>
                </span>
                <span class="arrow">›</span>
            </a>

        </div>
    </section>

</div>

{{-- RECENT ACTIVITY --}}
<section class="vet-card activity-section">

    <div class="section-heading">
        <div>
            <h2>Recent Appointment Activity</h2>
            <p class="vet-muted">Your latest appointment updates</p>
        </div>

        <a href="{{ url('/vet/appointments') }}"
           class="vet-btn vet-btn-secondary">
            View Appointments
        </a>
    </div>

    @forelse ($recentAppointments->take(5) as $appointment)

        <div class="activity-item">

            <div>
                <strong>{{ $appointment->pet?->pet_name ?? 'Pet' }}</strong>

                <p class="vet-muted">
                    {{ $appointment->service_type }}
                    ·
                    {{ $appointment->petOwner?->first_name }}
                    {{ $appointment->petOwner?->last_name }}
                </p>

                <small class="vet-muted">
                    {{ $appointment->appointment_at?->format('M j, Y · g:i A') }}
                </small>
            </div>

            @php
                $statusClass = match ($appointment->status) {
                    'pending' => 'pending',
                    'confirmed' => 'confirmed',
                    'cancelled' => 'cancelled',
                    'completed' => 'completed',
                    default => 'pending',
                };
            @endphp

            <span class="status-label {{ $statusClass }}">
                {{ ucfirst($appointment->status) }}
            </span>

        </div>

    @empty

        <div class="empty-state">
            <strong>No recent activity</strong>
            <p>Appointment updates will appear here.</p>
        </div>

    @endforelse

</section>

@endsection

@push('styles')
<style>
    /* STATISTICS */
    .vet-content .dashboard-stats {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 16px;
        width: 100%;
        margin-bottom: 22px;
    }

    .stat-card {
        min-width: 0;
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(50, 35, 85, .035);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 13px;
        font-size: 23px;
    }

    .stat-icon.purple {
        background: #f0eaff;
        color: #7652c8;
    }

    .stat-icon.orange {
        background: #fff3df;
        color: #c17a19;
    }

    .stat-icon.green {
        background: #e7f7ed;
        color: #29804a;
    }

    .stat-icon.blue {
        background: #e9f2ff;
        color: #3975bd;
    }

    .stat-card span {
        display: block;
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
    }

    .stat-card h2 {
        font-size: 27px;
        margin: 3px 0;
    }

    .stat-card small {
        color: var(--muted);
        font-size: 11px;
    }

    /* MAIN GRID */
    .vet-content .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .9fr);
        gap: 20px;
        align-items: start;
    }

    .section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .section-heading h2 {
        margin-bottom: 4px;
    }

    .section-heading p {
        font-size: 12px;
    }

    /* APPOINTMENTS */
    .appointment-item,
    .activity-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-top: 1px solid var(--border);
    }

    .appointment-date {
        width: 48px;
        height: 52px;
        flex-shrink: 0;
        border-radius: 10px;
        background: var(--purple-light);
        color: var(--purple);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .appointment-date strong {
        font-size: 20px;
        line-height: 1.1;
    }

    .appointment-date span {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .appointment-info {
        flex: 1;
        min-width: 0;
    }

    .appointment-info p,
    .activity-item p {
        color: var(--muted);
        font-size: 12px;
        margin: 4px 0;
    }

    .appointment-info small {
        color: var(--muted);
        font-size: 12px;
    }

    /* STATUS */
    .status-label {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-label.confirmed,
    .status-label.completed {
        color: #247344;
        background: #e7f7ed;
    }

    .status-label.pending {
        color: #946200;
        background: #fff4d9;
    }

    .status-label.cancelled {
        color: #b42318;
        background: #fff0f0;
    }

    /* QUICK ACTIONS */
    .quick-actions {
        display: grid;
        gap: 10px;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        border: 1px solid var(--border);
        border-radius: 11px;
        transition: .2s;
    }

    .quick-action:hover {
        background: var(--purple-light);
        border-color: #d9cafa;
    }

    .quick-icon {
        width: 39px;
        height: 39px;
        border-radius: 10px;
        background: var(--purple-light);
        color: var(--purple);
        display: grid;
        place-items: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .quick-action > span:nth-child(2) {
        flex: 1;
    }

    .quick-action strong,
    .quick-action small {
        display: block;
    }

    .quick-action strong {
        font-size: 13px;
    }

    .quick-action small {
        color: var(--muted);
        font-size: 11px;
        margin-top: 3px;
    }

    .quick-action .arrow {
        color: var(--muted);
        font-size: 22px;
    }

    /* RECENT ACTIVITY */
    .activity-section {
        margin-top: 20px;
    }

    .activity-item {
        justify-content: space-between;
    }

    /* EMPTY STATE */
    .empty-state {
        text-align: center;
        padding: 30px 15px;
        color: var(--muted);
    }

    .empty-state strong {
        color: var(--text);
    }

    .empty-state p {
        font-size: 12px;
        margin-top: 5px;
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
        .vet-content .dashboard-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 850px) {
        .vet-content .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 550px) {
        .vet-content .dashboard-stats {
            grid-template-columns: 1fr !important;
        }

        .appointment-item {
            flex-wrap: wrap;
        }

        .status-label {
            margin-left: 62px;
        }
    }
</style>
@endpush
