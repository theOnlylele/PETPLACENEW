
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Vet Portal') | PetPlace</title>

    <style>
        :root {
            --purple: #7652c8;
            --purple-dark: #6240ad;
            --purple-light: #f0eaff;
            --background: #f7f6fc;
            --white: #ffffff;
            --text: #302746;
            --muted: #81798e;
            --border: #eeeaf5;
            --green: #247344;
            --red: #b42318;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button, input, select, textarea {
            font: inherit;
        }

        .vet-layout {
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */
        .vet-sidebar {
            width: 250px;
            flex-shrink: 0;
            background: var(--white);
            border-right: 1px solid var(--border);
            padding: 25px 17px;
            display: flex;
            flex-direction: column;
        }

        .vet-brand {
            padding: 5px 12px 30px;
            font-size: 24px;
            font-weight: 800;
            color: var(--purple);
        }

        .vet-brand span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            margin-top: 3px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .vet-nav-label {
            padding: 0 12px;
            margin: 12px 0 8px;
            color: #a29bad;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .vet-nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .vet-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 13px;
            border-radius: 10px;
            color: #655d75;
            font-size: 14px;
            font-weight: 600;
            transition: .2s;
        }

        .vet-nav a:hover,
        .vet-nav a.active {
            background: var(--purple-light);
            color: var(--purple);
        }

        .vet-nav-icon {
            width: 22px;
            text-align: center;
            font-size: 17px;
        }

        .vet-sidebar-bottom {
            margin-top: auto;
            padding: 18px 12px 0;
            border-top: 1px solid var(--border);
        }

        .vet-user-name {
            font-size: 14px;
            font-weight: 700;
        }

        .vet-user-role {
            color: var(--muted);
            font-size: 12px;
            margin-top: 3px;
        }

        /* MAIN CONTENT */
        .vet-main {
            min-width: 0;
            flex: 1;
        }

        .vet-topbar {
            min-height: 76px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 15px 35px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .vet-topbar-title {
            font-size: 19px;
            font-weight: 700;
        }

        .vet-topbar-date {
            color: var(--muted);
            font-size: 13px;
            margin-top: 3px;
        }

        .vet-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .vet-logout {
            border: 0;
            border-radius: 9px;
            background: var(--purple-light);
            color: var(--purple);
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .vet-logout:hover {
            background: #e5d9ff;
        }

        .vet-content {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 30px 35px 50px;
        }

        .vet-page-heading {
            margin-bottom: 25px;
        }

        .vet-page-heading h1 {
            font-size: 27px;
            margin-bottom: 6px;
        }

        .vet-page-heading p {
            color: var(--muted);
            font-size: 14px;
        }

        /* SHARED MESSAGES */
        .vet-alert {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .vet-alert-success {
            background: #eaf8ef;
            color: var(--green);
            border: 1px solid #d4efdc;
        }

        .vet-alert-error {
            background: #fff0f0;
            color: var(--red);
            border: 1px solid #f5d2d2;
        }

        /* SHARED CARDS */
        .vet-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 23px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(50, 35, 85, .035);
        }

        .vet-card h2 {
            font-size: 19px;
            margin-bottom: 15px;
        }

        .vet-muted {
            color: var(--muted);
        }

        .vet-btn {
            display: inline-block;
            border: 0;
            border-radius: 9px;
            padding: 10px 16px;
            background: var(--purple);
            color: var(--white);
            font-weight: 700;
            cursor: pointer;
        }

        .vet-btn:hover {
            background: var(--purple-dark);
        }

        .vet-btn-secondary {
            background: var(--purple-light);
            color: var(--purple);
        }

        .vet-btn-secondary:hover {
            background: #e5d9ff;
        }

        /* RESPONSIVE */
        .vet-menu-toggle {
            display: none;
            border: 0;
            background: var(--purple-light);
            color: var(--purple);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 19px;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .vet-sidebar {
                width: 220px;
            }

            .vet-topbar,
            .vet-content {
                padding-left: 22px;
                padding-right: 22px;
            }
        }

        @media (max-width: 700px) {
            .vet-layout {
                display: block;
            }

            .vet-sidebar {
                display: none;
                width: 100%;
                border-right: 0;
                border-bottom: 1px solid var(--border);
            }

            .vet-sidebar.open {
                display: flex;
            }

            .vet-menu-toggle {
                display: inline-block;
            }

            .vet-topbar {
                padding: 14px 17px;
            }

            .vet-content {
                padding: 22px 16px 35px;
            }

            .vet-page-heading h1 {
                font-size: 23px;
            }
        }

        @stack('styles')
    </style>
</head>

<body>

<div class="vet-layout">

    <aside class="vet-sidebar" id="vetSidebar">

        <a href="{{ url('/vet/dashboard') }}" class="vet-brand">
            PetPlace
            <span>Veterinarian Portal</span>
        </a>

        <div class="vet-nav-label">Main Menu</div>

        <nav class="vet-nav">

            <a href="{{ url('/vet/dashboard') }}"
               class="{{ ($activePage ?? 'dashboard') === 'dashboard' ? 'active' : '' }}">
                <span class="vet-nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="{{ url('/vet/appointments') }}"
               class="{{ ($activePage ?? '') === 'appointments' ? 'active' : '' }}">
                <span class="vet-nav-icon">▣</span>
                Appointments
            </a>

            <a href="{{ url('/vet/schedule') }}"
               class="{{ ($activePage ?? '') === 'schedule' ? 'active' : '' }}">
                <span class="vet-nav-icon">▦</span>
                Schedule
            </a>

            <div class="vet-nav-label">Patient Care</div>

            <a href="{{ url('/vet/patients') }}"
               class="{{ ($activePage ?? '') === 'patients' ? 'active' : '' }}">
                <span class="vet-nav-icon">♡</span>
                Patients
            </a>

            <a href="{{ url('/vet/vaccinations') }}"
               class="{{ ($activePage ?? '') === 'vaccinations' ? 'active' : '' }}">
                <span class="vet-nav-icon">✚</span>
                Vaccinations
            </a>

            <!-- PATIENT HISTORY -->
            <a href="{{ route('vet.history') }}"
               class="{{ ($activePage ?? '') === 'history' ? 'active' : '' }}">
                <span class="vet-nav-icon">▤</span>
                Patient History
            </a>

            <div class="vet-nav-label">Account</div>

            <a href="{{ url('/vet/profile') }}"
               class="{{ ($activePage ?? '') === 'profile' ? 'active' : '' }}">
                <span class="vet-nav-icon">⚙</span>
                Profile & Availability
            </a>

        </nav>

        <div class="vet-sidebar-bottom">
            <div class="vet-user-name">
                Dr. {{ trim(($vet->first_name ?? '') . ' ' . ($vet->last_name ?? '')) ?: (auth()->user()->name ?? 'Veterinarian') }}
            </div>
            <div class="vet-user-role">Veterinarian</div>
        </div>

    </aside>

    <div class="vet-main">

        <header class="vet-topbar">

            <div style="display:flex;align-items:center;gap:12px;">

                <button type="button"
                        class="vet-menu-toggle"
                        onclick="document.getElementById('vetSidebar').classList.toggle('open')"
                        aria-label="Toggle navigation">
                    ☰
                </button>

                <div>
                    <div class="vet-topbar-title">@yield('page-title', 'Dashboard')</div>
                    <div class="vet-topbar-date">{{ now()->format('l, F j, Y') }}</div>
                </div>

            </div>

            <div class="vet-topbar-right">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="vet-logout">Log out</button>
                </form>
            </div>

        </header>

        <main class="vet-content">

            @if (session('status'))
                <div class="vet-alert vet-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="vet-alert vet-alert-error">
                    <strong>Please check the following:</strong>
                    <ul style="margin:8px 0 0 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>

    </div>
</div>

@stack('scripts')

</body>
</html>
