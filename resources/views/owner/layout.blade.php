
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'PetPlace') | PetPlace</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f6fc;
            color: #29243a;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button, input, select, textarea {
            font: inherit;
        }

        .top-header {
            background: #ffffff;
            padding: 17px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eeeaf5;
        }

        .brand {
            font-size: 25px;
            font-weight: 800;
            color: #7652c8;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .welcome-text {
            color: #686176;
            font-size: 14px;
        }

        .logout-button {
            background: #7652c8;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 10px 17px;
            cursor: pointer;
            font-weight: 600;
        }

        .logout-button:hover {
            background: #6240ad;
        }

        .owner-nav {
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 13px 4%;
            border-bottom: 1px solid #eeeaf5;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
        }

        .owner-nav a {
            padding: 11px 17px;
            border-radius: 9px;
            color: #655d75;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .owner-nav a:hover,
        .owner-nav a.active {
            background: #f0eaff;
            color: #7652c8;
        }

        .page-container {
            width: 90%;
            max-width: 1200px;
            margin: 35px auto;
            min-height: 65vh;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h1 {
            font-size: 29px;
            color: #302746;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #81798e;
            font-size: 15px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #eeeaf5;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 22px;
            box-shadow: 0 4px 15px rgba(50, 35, 85, 0.04);
        }

        .card h2 {
            font-size: 20px;
            color: #392d54;
            margin-bottom: 18px;
        }

        .btn {
            display: inline-block;
            background: #7652c8;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 11px 18px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn:hover {
            background: #6240ad;
        }

        .btn-secondary {
            display: inline-block;
            background: #f0eaff;
            color: #7652c8;
            border: none;
            border-radius: 9px;
            padding: 11px 18px;
            cursor: pointer;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #49415a;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ddd7e9;
            border-radius: 9px;
            background: #ffffff;
            outline: none;
        }

        .form-control:focus {
            border-color: #7652c8;
            box-shadow: 0 0 0 3px rgba(118, 82, 200, 0.1);
        }

        .status-message {
            background: #eaf8ef;
            color: #247344;
            padding: 14px 17px;
            border-radius: 9px;
            margin-bottom: 20px;
        }

        .error-message {
            background: #fff0f0;
            color: #b42318;
            padding: 14px 17px;
            border-radius: 9px;
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            padding: 25px;
            color: #91899e;
            font-size: 13px;
            border-top: 1px solid #eeeaf5;
            margin-top: 50px;
        }

        @media (max-width: 650px) {
            .top-header {
                padding: 15px 4%;
                flex-wrap: wrap;
                gap: 12px;
            }

            .brand {
                font-size: 21px;
            }

            .header-right {
                gap: 10px;
            }

            .owner-nav {
                justify-content: flex-start;
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .owner-nav a {
                white-space: nowrap;
            }

            .page-container {
                width: 93%;
                margin: 25px auto;
            }

            .card {
                padding: 18px;
            }
        }

        @stack('styles')
    </style>
</head>

<body>

    <header class="top-header">
        <a href="{{ route('owner.dashboard') }}" class="brand">
            PetPlace
        </a>

        <div class="header-right">
            <span class="welcome-text">
                Welcome, {{ auth()->user()->name }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">
                    Logout
                </button>
            </form>
        </div>
    </header>

    <nav class="owner-nav">
        <a href="{{ route('owner.dashboard') }}"
           class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
            Overview
        </a>

        <a href="{{ route('owner.pets') }}"
           class="{{ request()->routeIs('owner.pets') ? 'active' : '' }}">
            My Pets
        </a>

        <a href="{{ route('owner.pets.create') }}"
           class="{{ request()->routeIs('owner.pets.create') ? 'active' : '' }}">
            Add Pet
        </a>

        <a href="{{ route('owner.appointments') }}"
           class="{{ request()->routeIs('owner.appointments') ? 'active' : '' }}">
            Appointments
        </a>

        <a href="{{ route('owner.vaccinations') }}"
           class="{{ request()->routeIs('owner.vaccinations') ? 'active' : '' }}">
            Vaccination Records
        </a>
    </nav>

    <main class="page-container">

        @if (session('status'))
            <div class="status-message">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-message">
                <strong>Please check the following:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

    </main>

    <footer class="footer">
        © {{ date('Y') }} PetPlace Veterinary Clinic. All rights reserved.
    </footer>

    @stack('scripts')

</body>
</html>
