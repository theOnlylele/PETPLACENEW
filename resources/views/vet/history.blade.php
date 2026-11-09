
@extends('vet.layout')

@section('title', 'Patient History')
@section('page-title', 'Patient History')

@section('content')

<style>
    .history-heading {
        margin-bottom: 25px;
    }

    .history-heading h1 {
        font-size: 27px;
        margin-bottom: 6px;
    }

    .history-heading p {
        color: #81798e;
        font-size: 14px;
    }

    .history-search {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
    }

    .history-search input {
        flex: 1;
        min-width: 0;
        padding: 12px 15px;
        border: 1px solid #eeeaf5;
        border-radius: 9px;
        outline: none;
    }

    .history-search input:focus {
        border-color: #7652c8;
    }

    .history-search button {
        border: none;
        border-radius: 9px;
        padding: 12px 20px;
        background: #7652c8;
        color: white;
        font-weight: 700;
        cursor: pointer;
    }

    .history-search button:hover {
        background: #6240ad;
    }

    .history-card {
        background: white;
        border: 1px solid #eeeaf5;
        border-radius: 15px;
        padding: 23px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(50, 35, 85, .035);
    }

    .history-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .history-card-header h2 {
        font-size: 19px;
    }

    .history-count {
        background: #f0eaff;
        color: #7652c8;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .history-record {
        border: 1px solid #eeeaf5;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 14px;
    }

    .history-record:last-child {
        margin-bottom: 0;
    }

    .history-record-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 13px;
    }

    .history-pet-name {
        font-size: 17px;
        font-weight: 800;
        color: #302746;
    }

    .history-owner {
        color: #81798e;
        font-size: 13px;
        margin-top: 4px;
    }

    .history-date {
        color: #7652c8;
        background: #f0eaff;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .history-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .history-detail {
        background: #faf9fd;
        border-radius: 9px;
        padding: 12px;
        min-width: 0;
    }

    .history-detail.full {
        grid-column: 1 / -1;
    }

    .history-detail strong {
        display: block;
        color: #81798e;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 5px;
    }

    .history-detail span,
    .history-detail p {
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .history-empty {
        text-align: center;
        padding: 35px 15px;
        color: #81798e;
        font-size: 14px;
    }

    .history-empty-icon {
        font-size: 30px;
        margin-bottom: 10px;
    }

    .history-pagination {
        margin-top: 18px;
    }

    .history-pagination nav {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .history-pagination a,
    .history-pagination span {
        color: #7652c8;
    }

    @media (max-width: 600px) {
        .history-search {
            flex-direction: column;
        }

        .history-search button {
            width: 100%;
        }

        .history-record-top {
            flex-direction: column;
        }

        .history-details {
            grid-template-columns: 1fr;
        }

        .history-detail.full {
            grid-column: auto;
        }
    }
</style>

<div class="history-heading">
    <h1>Patient History</h1>
    <p>View consultation records and vaccination history of your patients.</p>
</div>

{{-- SEARCH --}}
<div class="history-card">
    <form method="GET" action="{{ route('vet.history') }}" class="history-search">
        <input
            type="text"
            name="search"
            value="{{ $search ?? request('search') }}"
            placeholder="Search by pet name or owner name..."
        >

        <button type="submit">Search</button>

        @if(request('search'))
            <a href="{{ route('vet.history') }}"
               style="display:flex;align-items:center;color:#81798e;font-size:13px;">
                Clear
            </a>
        @endif
    </form>
</div>

{{-- CONSULTATION HISTORY --}}
<div class="history-card">

    <div class="history-card-header">
        <h2>Consultation History</h2>
        <span class="history-count">
            {{ $visits->total() }} Records
        </span>
    </div>

    @forelse($visits as $visit)

        @php
            $pet = $visit->pet;
            $owner = $pet?->petOwner;

            $ownerName = trim(
                ($owner?->first_name ?? '') . ' ' .
                ($owner?->last_name ?? '')
            );

            $prescriptions = $visit->prescriptions ?? [];
        @endphp

        <div class="history-record">

            <div class="history-record-top">
                <div>
                    <div class="history-pet-name">
                        {{ $pet?->pet_name ?? 'Unknown Patient' }}
                    </div>

                    <div class="history-owner">
                        Owner: {{ $ownerName ?: 'Unknown Owner' }}
                        @if($pet?->pet_type)
                            | {{ ucfirst($pet->pet_type) }}
                        @endif
                        @if($pet?->breed)
                            | {{ $pet->breed }}
                        @endif
                    </div>
                </div>

                <div class="history-date">
                    {{ $visit->visit_date
                        ? \Illuminate\Support\Carbon::parse($visit->visit_date)->format('M d, Y')
                        : 'No date' }}
                </div>
            </div>

            <div class="history-details">

                <div class="history-detail">
                    <strong>Reason for Visit</strong>
                    <span>{{ $visit->reason ?: 'Not provided' }}</span>
                </div>

                <div class="history-detail">
                    <strong>Diagnosis</strong>
                    <span>{{ $visit->diagnosis ?: 'Not provided' }}</span>
                </div>

                <div class="history-detail full">
                    <strong>Treatment</strong>
                    <p>{{ $visit->treatment ?: 'No treatment recorded.' }}</p>
                </div>

                @if(!empty($prescriptions))
                    <div class="history-detail full">
                        <strong>Prescriptions</strong>

                        @foreach($prescriptions as $prescription)
                            <p style="margin-bottom:8px;">
                                <strong style="display:inline;color:#302746;text-transform:none;">
                                    {{ $prescription['medicine'] ?? 'Medicine' }}
                                </strong>

                                @if(!empty($prescription['dosage']))
                                    — {{ $prescription['dosage'] }}
                                @endif

                                @if(!empty($prescription['duration']))
                                    — {{ $prescription['duration'] }}
                                @endif

                                @if(!empty($prescription['instructions']))
                                    <br>
                                    <span class="vet-muted">
                                        {{ $prescription['instructions'] }}
                                    </span>
                                @endif
                            </p>
                        @endforeach
                    </div>
                @else
                    <div class="history-detail">
                        <strong>Prescriptions</strong>
                        <span>No prescriptions recorded.</span>
                    </div>
                @endif

                @if($visit->follow_up_date)
                    <div class="history-detail">
                        <strong>Follow-up Date</strong>
                        <span>
                            {{ \Illuminate\Support\Carbon::parse($visit->follow_up_date)->format('M d, Y') }}
                        </span>
                    </div>
                @endif

                <div class="history-detail">
                    <strong>Veterinarian</strong>
                    <span>
                        Dr. {{ trim(($visit->veterinarian?->first_name ?? '') . ' ' . ($visit->veterinarian?->last_name ?? '')) ?: 'Veterinarian' }}
                    </span>
                </div>

            </div>
        </div>

    @empty

        <div class="history-empty">
            <div class="history-empty-icon">📋</div>
            <strong>No consultation history found.</strong>
            <p style="margin-top:6px;">
                Completed consultations will appear here.
            </p>
        </div>

    @endforelse

    @if($visits->hasPages())
        <div class="history-pagination">
            {{ $visits->withQueryString()->links() }}
        </div>
    @endif

</div>

{{-- VACCINATION HISTORY --}}
<div class="history-card">

    <div class="history-card-header">
        <h2>Vaccination History</h2>
        <span class="history-count">
            {{ $vaccinations->total() }} Records
        </span>
    </div>

    @forelse($vaccinations as $vaccination)

        @php
            $pet = $vaccination->pet;
            $owner = $pet?->petOwner;

            $ownerName = trim(
                ($owner?->first_name ?? '') . ' ' .
                ($owner?->last_name ?? '')
            );
        @endphp

        <div class="history-record">

            <div class="history-record-top">
                <div>
                    <div class="history-pet-name">
                        {{ $pet?->pet_name ?? 'Unknown Patient' }}
                    </div>

                    <div class="history-owner">
                        Owner: {{ $ownerName ?: 'Unknown Owner' }}
                    </div>
                </div>

                <div class="history-date">
                    {{ $vaccination->date_given
                        ? \Illuminate\Support\Carbon::parse($vaccination->date_given)->format('M d, Y')
                        : 'No date' }}
                </div>
            </div>

            <div class="history-details">

                <div class="history-detail">
                    <strong>Vaccine</strong>
                    <span>
                        {{ \Illuminate\Support\Str::headline($vaccination->vaccine_key ?? 'Vaccine') }}
                    </span>
                </div>

                <div class="history-detail">
                    <strong>Category</strong>
                    <span>{{ $vaccination->category ?: 'Core' }}</span>
                </div>

                <div class="history-detail">
                    <strong>Date Given</strong>
                    <span>
                        {{ $vaccination->date_given
                            ? \Illuminate\Support\Carbon::parse($vaccination->date_given)->format('M d, Y')
                            : 'Not recorded' }}
                    </span>
                </div>

                <div class="history-detail">
                    <strong>Next Due Date</strong>
                    <span>
                        {{ $vaccination->next_due_date
                            ? \Illuminate\Support\Carbon::parse($vaccination->next_due_date)->format('M d, Y')
                            : 'Not set' }}
                    </span>
                </div>

                <div class="history-detail">
                    <strong>Dose Number</strong>
                    <span>{{ $vaccination->dose_number ?: 'Not specified' }}</span>
                </div>

                <div class="history-detail">
                    <strong>Brand</strong>
                    <span>{{ $vaccination->brand ?: 'Not specified' }}</span>
                </div>

                <div class="history-detail">
                    <strong>Batch Number</strong>
                    <span>{{ $vaccination->batch_number ?: 'Not specified' }}</span>
                </div>

                <div class="history-detail">
                    <strong>Veterinarian</strong>
                    <span>
                        {{ $vaccination->veterinarian_name
                            ?: 'Not specified' }}
                    </span>
                </div>

                @if($vaccination->notes)
                    <div class="history-detail full">
                        <strong>Notes</strong>
                        <p>{{ $vaccination->notes }}</p>
                    </div>
                @endif

            </div>
        </div>

    @empty

        <div class="history-empty">
            <div class="history-empty-icon">💉</div>
            <strong>No vaccination history found.</strong>
            <p style="margin-top:6px;">
                Recorded vaccinations will appear here.
            </p>
        </div>

    @endforelse

    @if($vaccinations->hasPages())
        <div class="history-pagination">
            {{ $vaccinations->withQueryString()->links() }}
        </div>
    @endif

</div>

@endsection
