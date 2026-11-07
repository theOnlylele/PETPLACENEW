
    @extends('owner.layout')

    @section('title', 'Overview')

    @section('content')
    <div class="page-heading">
        <h1>Owner Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}! Here's your PetPlace overview.</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">

        <div class="card">
            <h2>My Pets</h2>
            <p style="font-size:32px; font-weight:800; color:#7652c8;">
                {{ $pets->count() }}
            </p>
            <p style="color:#81798e; margin:10px 0 18px;">Registered pets</p>
            <a href="{{ route('owner.pets') }}" class="btn">View My Pets</a>
        </div>

        <div class="card">
            <h2>Appointments</h2>
            <p style="font-size:32px; font-weight:800; color:#7652c8;">
                {{ $appointments->count() }}
            </p>
            <p style="color:#81798e; margin:10px 0 18px;">Your appointment records</p>
            <a href="{{ route('owner.appointments') }}" class="btn">View Appointments</a>
        </div>

        <div class="card">
            <h2>Vaccinations</h2>
            <p style="font-size:32px; font-weight:800; color:#7652c8;">
                {{ $vaccinationRecords->count() }}
            </p>
            <p style="color:#81798e; margin:10px 0 18px;">Vaccination records</p>
            <a href="{{ route('owner.vaccinations') }}" class="btn">View Vaccinations</a>
        </div>

    </div>

    <div class="card" style="margin-top:25px;">
        <h2>Quick Actions</h2>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('owner.pets.create') }}" class="btn">+ Add Pet</a>
            <a href="{{ route('owner.appointments') }}" class="btn-secondary">Manage Appointments</a>
            <a href="{{ route('owner.vaccinations') }}" class="btn-secondary">View Vaccination Records</a>
        </div>
    </div>
    @endsection
