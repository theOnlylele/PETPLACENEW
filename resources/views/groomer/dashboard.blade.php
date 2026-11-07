<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groomer Dashboard | PetPlace</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#eef4fd; --white:#fff; --border:#c8dff5; --text:#0f2137; --muted:#4a6080; --accent:#4f86c6; --accent-dark:#3d75b5; --pale:#deeaf8; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; padding:24px; color:var(--text); font:15px/1.5 'DM Sans',sans-serif; background:var(--bg); background-image:radial-gradient(circle at 15% 15%,rgba(79,134,198,.1),transparent 50%),radial-gradient(circle at 85% 85%,rgba(79,134,198,.06),transparent 50%); }
        main { max-width:1100px; margin:0 auto; }
        header,.heading,.card,.appointment { background:var(--white); border:1px solid var(--border); border-radius:16px; }
        header { padding:18px 22px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand { color:var(--accent); font-weight:800; font-size:20px; }
        h1 { margin:18px 0 4px; font-size:28px; }
        h2 { margin:0 0 12px; font-size:19px; }
        p { margin:5px 0; }
        .muted { color:var(--muted); }
        .logout { border:1px solid var(--border); background:#fff; color:var(--muted); border-radius:9px; padding:9px 13px; font:600 14px 'DM Sans',sans-serif; cursor:pointer; }
        .overview { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin:20px 0; }
        .card { padding:18px; box-shadow:0 4px 18px rgba(79,134,198,.06); }
        .metric { color:var(--accent); font-size:27px; font-weight:800; }
        .appointments { display:grid; gap:12px; }
        .appointment { padding:17px; }
        .appointment-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .badge { display:inline-block; padding:4px 10px; border-radius:30px; background:var(--pale); color:var(--accent-dark); font-size:12px; font-weight:700; }
        .badge.pending { background:#fff4ce; color:#8a6500; }
        .badge.confirmed { background:#e4f4e8; color:#21653f; }
        .badge.cancelled { background:#fdeaea; color:#994141; }
        .details { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px 18px; margin-top:10px; }
        @media(max-width:600px) { body{padding:14px}.overview{gap:8px}.card{padding:13px}.details{grid-template-columns:1fr}header{padding:15px}h1{font-size:24px} }
    </style>
</head>
<body>
<main>
    <header>
        <div class="brand">🐾 Pet Place <span class="muted">· Groomer Portal</span></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">Log out</button>
        </form>
    </header>

    <h1>Welcome, {{ $groomer->first_name }}</h1>
    <p class="muted">Your grooming appointments and incoming requests.</p>

    <section class="overview" aria-label="Groomer overview">
        <div class="card"><div class="metric">{{ $todayCount }}</div><div class="muted">Confirmed appointments today</div></div>
        <div class="card"><div class="metric">{{ $pendingCount }}</div><div class="muted">Pending grooming requests</div></div>
    </section>

    <section class="card">
        <h2>Grooming appointments</h2>
        @if ($appointments->isEmpty())
            <p class="muted">There are no grooming appointments yet.</p>
        @else
            <div class="appointments">
                @foreach ($appointments as $appointment)
                    @php($statusClass = in_array($appointment->status, ['pending', 'confirmed', 'cancelled'], true) ? $appointment->status : '')
                    <article class="appointment">
                        <div class="appointment-head">
                            <strong>{{ $appointment->pet?->pet_name ?? 'Pet' }}</strong>
                            <span class="badge {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                        </div>
                        <div class="details">
                            <div><strong>When:</strong> {{ $appointment->appointment_at?->format('M j, Y g:i A') }}</div>
                            <div><strong>Owner:</strong> {{ $appointment->petOwner?->first_name }} {{ $appointment->petOwner?->last_name }}</div>
                            <div><strong>Pet:</strong> {{ ucfirst($appointment->pet?->pet_type ?? 'Pet') }}{{ $appointment->pet_size ? ' · '.$appointment->pet_size : '' }}</div>
                            <div><strong>Coat:</strong> {{ $appointment->coat_type ?: 'Not specified' }}</div>
                            @if ($appointment->haircut_style)<div><strong>Haircut:</strong> {{ $appointment->haircut_style }}</div>@endif
                            @if ($appointment->shampoo_type)<div><strong>Shampoo:</strong> {{ $appointment->shampoo_type }}</div>@endif
                            @if ($appointment->grooming_addons)<div><strong>Add-ons:</strong> {{ collect($appointment->grooming_addons)->map(fn ($addon) => str($addon)->replace('_', ' ')->title())->join(', ') }}</div>@endif
                            @if ($appointment->reason)<div><strong>Client notes:</strong> {{ $appointment->reason }}</div>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</main>
</body>
</html>
