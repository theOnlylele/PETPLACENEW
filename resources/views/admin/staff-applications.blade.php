```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Applications | PetPlace</title>

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
            margin-bottom: 35px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #28564b;
        }

        .main {
            margin-left: 240px;
            padding: 40px;
        }

        .header {
            margin-bottom: 25px;
        }

        .application {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px #0000000a;
        }

        .application h3 {
            margin-bottom: 12px;
        }

        .application p {
            margin-bottom: 8px;
            color: #64748b;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
        }

        .approve {
            background: #198754;
        }

        .reject {
            background: #dc3545;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .empty {
            background: white;
            padding: 30px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <h2>PetPlace</h2>

    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
    <a href="{{ route('admin.staff-applications') }}" class="active">Staff Applications</a>
</aside>

<main class="main">

    <div class="header">
        <h1>Staff Applications</h1>
        <p>Review and manage veterinarian and groomer applications.</p>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    @forelse($applications as $application)

        <div class="application">
            <h3>
                {{ $application->first_name }}
                {{ $application->last_name }}
            </h3>

            <p><strong>Email:</strong> {{ $application->user->email }}</p>
            <p><strong>Staff Type:</strong> {{ ucfirst($application->staff_type) }}</p>
            <p><strong>License Number:</strong> {{ $application->license_number ?? 'Not provided' }}</p>
            <p><strong>Specialty:</strong> {{ $application->specialty ?? 'Not provided' }}</p>
            <p><strong>Clinic:</strong> {{ $application->clinic_name ?? 'Not provided' }}</p>
            <p><strong>Applied:</strong> {{ $application->created_at->format('M d, Y') }}</p>

            <div class="actions">
                <form method="POST" action="{{ route('admin.staff-approve', $application->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="approve">Approve</button>
                </form>

                <form method="POST" action="{{ route('admin.staff-reject', $application->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="reject">Reject</button>
                </form>
            </div>
        </div>

    @empty

        <div class="empty">
            <h3>No pending applications</h3>
            <p>There are currently no staff applications waiting for review.</p>
        </div>

    @endforelse

</main>

</body>
</html>
```