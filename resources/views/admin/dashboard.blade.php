
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetPlace Admin Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #263445;
        }

        .sidebar {
            position: fixed;
            width: 240px;
            height: 100vh;
            background: #173b35;
            color: white;
            padding: 30px 20px;
        }

        .sidebar h2 {
            margin-bottom: 40px;
        }

        .sidebar a {
            display: block;
            padding: 14px;
            margin-top: 15px;
            background: #28564b;
            border-radius: 8px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #397261;
        }

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px #0000000a;
        }

        .card h3 {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
        }

        .card a {
            display: inline-block;
            margin-top: 15px;
            color: #16834b;
            text-decoration: none;
            font-weight: bold;
        }

        .logout {
            margin-top: 40px;
        }

        .logout button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <h2>PetPlace</h2>

        <a href="{{ route('admin.dashboard') }}">
            Admin Dashboard
        </a>

        <a href="{{ route('admin.staff-applications') }}">
            Staff Applications
        </a>

        <form class="logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log Out</button>
        </form>
    </aside>

    <main class="main">

        <div class="header">
            <h1>Welcome, Administrator!</h1>
            <p>Manage your PetPlace platform.</p>
        </div>

        <div class="cards">

            <div class="card">
                <h3>Administrator</h3>
                <p>{{ auth()->user()->name }}</p>
            </div>

            <div class="card">
                <h3>Account Role</h3>
                <p>Admin</p>
            </div>

            <div class="card">
                <h3>Pending Staff Applications</h3>
                <p>
                    {{ \App\Models\Vet::where('approval_status', 'pending')->count() }}
                </p>

                <a href="{{ route('admin.staff-applications') }}">
                    Review Applications →
                </a>
            </div>

        </div>

    </main>

</body>
</html>
