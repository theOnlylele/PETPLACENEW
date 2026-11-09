<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groomer Dashboard | PetPlace</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #eef4fd;
            --white: #fff;
            --border: #c8dff5;
            --text: #0f2137;
            --muted: #4a6080;
            --accent: #4f86c6;
            --accent-dark: #3d75b5;
            --pale: #deeaf8;
            --green: #21653f;
            --green-bg: #e4f4e8;
            --yellow: #8a6500;
            --yellow-bg: #fff4ce;
            --red: #994141;
            --red-bg: #fdeaea;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            color: var(--text);
            font: 15px/1.5 'DM Sans', sans-serif;
            background: var(--bg);
            background-image:
                radial-gradient(circle at 15% 15%, rgba(79,134,198,.1), transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(79,134,198,.06), transparent 50%);
        }

        main { max-width: 1100px; margin: 0 auto; }
        header, .card, .appointment { background: var(--white); border: 1px solid var(--border); border-radius: 16px; }
        header { padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .brand { color: var(--accent); font-weight: 800; font-size: 20px; }
        h1 { margin: 20px 0 4px; font-size: 28px; }
        h2 { margin: 0 0 14px; font-size: 19px; }
        h3 { margin: 0; font-size: 17px; }
        p { margin: 5px 0; }
        .muted { color: var(--muted); }
        .logout, .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--muted);
            border-radius: 9px;
            padding: 9px 13px;
            font: 600 14px 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
        }
        .button-primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .button-danger { background: var(--red-bg); border-color: #f3caca; color: var(--red); }
        .overview { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 20px 0; }
        .card { padding: 18px; box-shadow: 0 4px 18px rgba(79,134,198,.06); }
        .metric { color: var(--accent); font-size: 27px; font-weight: 800; }
        .appointments { display: grid; gap: 14px; }
        .appointment { padding: 18px; }
        .appointment-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 30px; background: var(--pale); color: var(--accent-dark); font-size: 12px; font-weight: 700; white-space: nowrap; }
        .badge.pending { background: var(--yellow-bg); color: var(--yellow); }
        .badge.confirmed { background: var(--green-bg); color: var(--green); }
        .badge.cancelled { background: var(--red-bg); color: var(--red); }
        .badge.completed { background: #e8e8f7; color: #4b4b83; }
        .details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 18px; margin-top: 14px; }
        .detail { overflow-wrap: anywhere; }
        .actions { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #e8eff7; }
        .decline-form { display: flex; flex: 1 1 100%; flex-wrap: wrap; gap: 9px; align-items: center; }
        .decline-form input { flex: 1 1 230px; min-width: 0; border: 1px solid var(--border); border-radius: 9px; padding: 10px 12px; font: 14px 'DM Sans', sans-serif; }
        .alert { margin: 16px 0; padding: 12px 15px; border-radius: 10px; background: var(--green-bg); color: var(--green); border: 1px solid #c9e8d1; }
        .errors { margin: 16px 0; padding: 12px 15px; border-radius: 10px; background: var(--red-bg); color: var(--red); border: 1px solid #f3caca; }
        .section-note { margin: -6px 0 15px; }

        @media (max-width: 600px) {
            body { padding: 14px; }
            header { padding: 15px; }
            h1 { font-size: 24px; }
            .overview { gap: 8px; }
            .card { padding: 13px; }
            .details { grid-template-columns: 1fr; }
            .appointment { padding: 14px; }
            .appointment-head { align-items: flex-start; }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div class="brand">PetPlace <span class="muted">· Groomer Portal</span></div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('groomer.schedule') }}" class="logout">My Schedule</a>
            <form method="POST" action="{{ route('logout') }}">
            @csrf
                <button type="submit" class="logout">Log out</button>
            </form>
        </div>
    </header>

    <h1>Welcome, {{ $groomer->first_name }}</h1>
    <p class="muted">Review incoming grooming requests and manage your appointments.</p>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="overview" aria-label="Groomer overview">
        <div class="card">
            <div class="metric">{{ $todayCount }}</div>
            <div class="muted">Confirmed appointments today</div>
        </div>
        <div class="card">
            <div class="metric">{{ $pendingCount }}</div>
            <div class="muted">Pending grooming requests</div>
        </div>
    </section>

    <section class="card">
        <h2>Grooming appointments</h2>
        <p class="muted section-note">Accept a request to assign it to yourself. Only the assigned groomer can mark it as completed.</p>

        @if ($appointments->isEmpty())
            <p class="muted">There are no grooming appointments yet.</p>
        @else
            <div class="appointments">
                @foreach ($appointments as $appointment)
                    @php
                        $statusClass = in_array($appointment->status, ['pending', 'confirmed', 'completed', 'cancelled'], true)
                            ? $appointment->status
                            : '';
                    @endphp

                    <article class="appointment">
                        <div class="appointment-head">
                            <div>
                                <h3>{{ $appointment->pet?->pet_name ?? 'Pet' }}</h3>
                                <p class="muted">Request #{{ $appointment->id }}</p>
                            </div>
                            <span class="badge {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                        </div>

                        <div class="details">
                            <div class="detail"><strong>Appointment:</strong> {{ $appointment->appointment_at?->format('M j, Y g:i A') ?? 'Not set' }}</div>
                            <div class="detail"><strong>Owner:</strong> {{ trim(($appointment->petOwner?->first_name ?? '').' '.($appointment->petOwner?->last_name ?? '')) ?: 'Not available' }}</div>
                            <div class="detail"><strong>Animal:</strong> {{ ucfirst($appointment->pet?->pet_type ?? 'Not specified') }}</div>
                            <div class="detail"><strong>Pet size:</strong> {{ $appointment->pet_size ?: 'Not specified' }}</div>
                            <div class="detail"><strong>Coat:</strong> {{ $appointment->coat_type ?: 'Not specified' }}</div>
                            <div class="detail"><strong>Haircut:</strong> {{ $appointment->haircut_style ?: 'Not specified' }}</div>
                            <div class="detail"><strong>Shampoo:</strong> {{ $appointment->shampoo_type ?: 'Not specified' }}</div>
                            <div class="detail"><strong>Temperament:</strong> {{ $appointment->temperament ?: 'Not specified' }}</div>

                            @if ($appointment->fur_matted !== null)
                                <div class="detail"><strong>Matted fur:</strong> {{ $appointment->fur_matted ? 'Yes' : 'No' }}</div>
                            @endif

                            @if (!empty($appointment->grooming_addons))
                                <div class="detail"><strong>Add-ons:</strong> {{ collect($appointment->grooming_addons)->map(fn ($addon) => str($addon)->replace('_', ' ')->title())->join(', ') }}</div>
                            @endif

                            @if ($appointment->allergies_or_skin_problems)
                                <div class="detail"><strong>Allergies / skin concerns:</strong> {{ $appointment->allergies_or_skin_problems }}</div>
                            @endif

                            @if ($appointment->reason)
                                <div class="detail"><strong>Owner notes:</strong> {{ $appointment->reason }}</div>
                            @endif

                            @if ($appointment->decision_reason)
                                <div class="detail"><strong>Decision note:</strong> {{ $appointment->decision_reason }}</div>
                            @endif
                        </div>

                        @if ($appointment->status === 'pending')
                            <div class="actions">
                                <form method="POST" action="{{ route('groomer.appointments.update', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="button button-primary">Accept request</button>
                                </form>

                                <form method="POST" action="{{ route('groomer.appointments.update', $appointment) }}" class="decline-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="decline">
                                    <input type="text" name="decision_reason" maxlength="1000" placeholder="Reason for declining" required>
                                    <button type="submit" class="button button-danger">Decline</button>
                                </form>
                            </div>
                        @elseif ($appointment->status === 'confirmed' && (int) $appointment->groomer_id === (int) $groomer->id)
                            <div class="actions">
                                <form method="POST" action="{{ route('groomer.appointments.update', $appointment) }}" onsubmit="return confirm('Mark this grooming appointment as completed?');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="button button-primary">Mark as completed</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</main>
</body>
</html>
