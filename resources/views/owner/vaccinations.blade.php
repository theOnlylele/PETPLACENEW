
@extends('owner.layout')

@section('title', 'Vaccination Records')

@section('content')

<div class="page-heading">
    <h1>Vaccination Records</h1>
    <p>Monitor your pet's vaccination history and upcoming vaccinations.</p>
</div>

{{-- PET SELECTOR --}}
<div class="card">
    <div class="form-group" style="margin-bottom:0;">
        <label for="pet-selector">Select Pet</label>
        <select id="pet-selector" class="form-control"
                onchange="window.location.href=this.value">
            @foreach ($pets as $listedPet)
                <option
                    value="{{ route('owner.vaccinations', ['pet_id' => $listedPet->id]) }}"
                    {{ $pet->id == $listedPet->id ? 'selected' : '' }}>
                    {{ $listedPet->pet_name }} ({{ $listedPet->pet_type }})
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- VACCINATION SUMMARY --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:18px;">
    <div class="card">
        <p style="color:#81798e; font-size:14px;">Core Vaccines</p>
        <h2 style="font-size:30px; color:#7652c8; margin:10px 0;">
            {{ $vaccinationSummary['coreTotal'] }}
        </h2>
        <p style="color:#81798e; font-size:13px;">Required core vaccine categories</p>
    </div>

    <div class="card">
        <p style="color:#81798e; font-size:14px;">Up-to-date Core Vaccines</p>
        <h2 style="font-size:30px; color:#247344; margin:10px 0;">
            {{ $vaccinationSummary['coreUpToDate'] }}
        </h2>
        <p style="color:#81798e; font-size:13px;">Core categories currently up to date</p>
    </div>

    <div class="card">
        <p style="color:#81798e; font-size:14px;">Recorded Vaccinations</p>
        <h2 style="font-size:30px; color:#7652c8; margin:10px 0;">
            {{ collect($vaccinationRows)->filter(fn ($row) => $row['record'] !== null)->count() }}
        </h2>
        <p style="color:#81798e; font-size:13px;">Saved vaccination records</p>
    </div>
</div>

{{-- UPCOMING VACCINATION --}}
@if ($vaccinationSummary['nextDueRecord'])
    <div class="card">
        <h2>Upcoming Vaccination</h2>

        <p style="color:#655d75; margin-bottom:8px;">
            <strong>
                {{ $vaccineLabels[$vaccinationSummary['nextDueRecord']->vaccine_key]
                    ?? $vaccinationSummary['nextDueRecord']->vaccine_key }}
            </strong>
        </p>

        <p style="color:#81798e;">
            Next due:
            {{ $vaccinationSummary['nextDueRecord']->next_due_date->format('F d, Y') }}
        </p>
    </div>
@endif

{{-- VACCINATION STATUS --}}
<div class="card">
    <h2>Vaccination Status</h2>

    @if (count($vaccinationRows) === 0)
        <p style="color:#81798e;">No vaccination information available.</p>
    @else
        @foreach ($vaccinationRows as $row)
            @php
                $record = $row['record'];
                $status = $row['status'];

                $statusColors = [
                    'up_to_date' => ['#eaf8ef', '#247344', 'Up to date'],
                    'due_soon' => ['#fff7e6', '#a66a00', 'Due soon'],
                    'overdue' => ['#fff0f0', '#b42318', 'Overdue'],
                    'not_yet_given' => ['#f0eaff', '#7652c8', 'Not yet given'],
                ];

                $displayStatus = $statusColors[$status] ?? $statusColors['not_yet_given'];
            @endphp

            <div style="padding:17px 0; border-bottom:1px solid #eeeaf5;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <div>
                        <strong style="color:#392d54;">
                            {{ $vaccineLabels[$row['vaccine_key']] ?? $row['vaccine_key'] }}
                        </strong>

                        <p style="font-size:12px; color:#81798e; margin-top:5px;">
                            {{ $vaccineCategories[$row['vaccine_key']] ?? 'Vaccination record' }}
                        </p>
                    </div>

                    <span style="background:{{ $displayStatus[0] }}; color:{{ $displayStatus[1] }}; padding:7px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                        {{ $displayStatus[2] }}
                    </span>
                </div>

                @if ($record)
                    <div style="font-size:13px; color:#81798e; margin-top:10px; line-height:1.8;">
                        <p>
                            Date given:
                            {{ $record->date_given
                                ? $record->date_given->format('F d, Y')
                                : 'Not specified' }}
                        </p>

                        <p>
                            Next due:
                            {{ $record->next_due_date
                                ? $record->next_due_date->format('F d, Y')
                                : 'Not specified' }}
                        </p>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>

<div class="card">
    <h2>Need a Vaccination?</h2>
    <p style="color:#81798e; margin-bottom:18px;">
        Request a vaccination appointment for {{ $pet->pet_name }}.
    </p>

    <a href="{{ route('owner.appointments') }}" class="btn">
        Request Vaccination Appointment
    </a>
</div>

@endsection
