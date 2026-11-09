@extends('vet.layout')

@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')

<div class="vet-page-heading">
    <div>
        <h1>My Patients</h1>
        <p>View your patients, their information, and medical history.</p>
    </div>
</div>

@if(session('status'))
    <div class="vet-alert vet-alert-success">
        {{ session('status') }}
    </div>
@endif

{{-- PATIENT SEARCH --}}
<section class="vet-card">
    <div class="section-heading">
        <div>
            <h2>Patient Directory</h2>
            <p class="vet-muted">
                Search and manage pets assigned to your care.
            </p>
        </div>

        <span class="vet-badge">
            {{ $patients->total() }} Patients
        </span>
    </div>

    <form method="GET" action="{{ route('vet.patients') }}" class="vet-search-form">
        <input
            type="text"
            name="search"
            value="{{ $search ?? '' }}"
            placeholder="Search pet name or owner name..."
            class="vet-input"
        >

        <button type="submit" class="vet-button">
            Search
        </button>

        @if(request()->filled('search'))
            <a href="{{ route('vet.patients') }}" class="vet-button vet-button-light">
                Clear
            </a>
        @endif
    </form>

    {{-- PATIENT TABLE --}}
    <div class="vet-table-wrapper">
        <table class="vet-table">
            <thead>
                <tr>
                    <th>Pet</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Owner</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($patients as $pet)
                    <tr>
                        <td>
                            <div class="vet-patient-name">
                                @if($pet->photo)
                                    <img
                                        src="{{ asset('storage/'.$pet->photo) }}"
                                        alt="{{ $pet->pet_name }}"
                                        class="vet-patient-avatar"
                                    >
                                @else
                                    <div class="vet-patient-avatar vet-patient-placeholder">
                                        <span>{{ strtoupper(substr($pet->pet_name, 0, 1)) }}</span>
                                    </div>
                                @endif

                                <strong>{{ $pet->pet_name }}</strong>
                            </div>
                        </td>

                        <td>{{ ucfirst($pet->pet_type ?? 'Not specified') }}</td>

                        <td>{{ $pet->breed ?: 'Not specified' }}</td>

                        <td>
                            {{ $pet->petOwner?->first_name }}
                            {{ $pet->petOwner?->last_name }}
                        </td>

                        <td>
                            <a
                                href="{{ route('vet.patients', array_merge(request()->query(), ['patient_id' => $pet->id])) }}"
                                class="vet-button vet-button-small"
                            >
                                View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="vet-empty-state">
                            No patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="vet-pagination">
        {{ $patients->links() }}
    </div>
</section>

{{-- SELECTED PATIENT DETAILS --}}
@if($selectedPatient)

    <section class="vet-card vet-patient-details">

        <div class="section-heading">
            <div>
                <h2>Patient Information</h2>
                <p class="vet-muted">Complete details about this patient.</p>
            </div>

            <a href="{{ route('vet.patients', request()->query()) }}"
               class="vet-button vet-button-light">
                Close Details
            </a>
        </div>

        <div class="vet-patient-profile">

            @if($selectedPatient->photo)
                <img
                    src="{{ asset('storage/'.$selectedPatient->photo) }}"
                    alt="{{ $selectedPatient->pet_name }}"
                    class="vet-patient-profile-image"
                >
            @else
                <div class="vet-patient-profile-image vet-patient-placeholder">
                    {{ strtoupper(substr($selectedPatient->pet_name, 0, 1)) }}
                </div>
            @endif

            <div>
                <h2>{{ $selectedPatient->pet_name }}</h2>
                <p>
                    {{ ucfirst($selectedPatient->pet_type ?? 'Unknown') }}
                    ·
                    {{ $selectedPatient->breed ?: 'Breed not specified' }}
                </p>
            </div>
        </div>

        <div class="vet-detail-grid">

            <div class="vet-detail-item">
                <span>Pet Name</span>
                <strong>{{ $selectedPatient->pet_name }}</strong>
            </div>

            <div class="vet-detail-item">
                <span>Pet Type</span>
                <strong>{{ ucfirst($selectedPatient->pet_type ?? 'Not specified') }}</strong>
            </div>

            <div class="vet-detail-item">
                <span>Breed</span>
                <strong>{{ $selectedPatient->breed ?: 'Not specified' }}</strong>
            </div>

            <div class="vet-detail-item">
                <span>Weight</span>
                <strong>
                    {{ $selectedPatient->weight ? $selectedPatient->weight.' kg' : 'Not recorded' }}
                </strong>
            </div>

            <div class="vet-detail-item">
                <span>Owner</span>
                <strong>
                    {{ $selectedPatient->petOwner?->first_name }}
                    {{ $selectedPatient->petOwner?->last_name }}
                </strong>
            </div>

            <div class="vet-detail-item">
                <span>Owner Email</span>
                <strong>
                    {{ $selectedPatient->petOwner?->user?->email ?? 'Not available' }}
                </strong>
            </div>

            <div class="vet-detail-item">
                <span>Allergies</span>
                <strong>{{ $selectedPatient->allergies ?: 'None recorded' }}</strong>
            </div>

        </div>
    </section>

    {{-- VISIT HISTORY --}}
    <section class="vet-card">
        <div class="section-heading">
            <div>
                <h2>Visit History</h2>
                <p class="vet-muted">Previous consultations and treatments.</p>
            </div>
        </div>

        <div class="vet-table-wrapper">
            <table class="vet-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Diagnosis</th>
                        <th>Treatment</th>
                        <th>Follow-up</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($selectedPatientVisits as $visit)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($visit->visit_date)->format('M d, Y') }}</td>
                            <td>{{ $visit->reason ?: '—' }}</td>
                            <td>{{ $visit->diagnosis ?: '—' }}</td>
                            <td>{{ $visit->treatment ?: '—' }}</td>
                            <td>
                                {{ $visit->follow_up_date
                                    ? \Illuminate\Support\Carbon::parse($visit->follow_up_date)->format('M d, Y')
                                    : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="vet-empty-state">
                                No visit records available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- VACCINATION HISTORY --}}
    <section class="vet-card">
        <div class="section-heading">
            <div>
                <h2>Vaccination History</h2>
                <p class="vet-muted">Recorded vaccines and upcoming due dates.</p>
            </div>
        </div>

        <div class="vet-table-wrapper">
            <table class="vet-table">
                <thead>
                    <tr>
                        <th>Vaccine</th>
                        <th>Category</th>
                        <th>Date Given</th>
                        <th>Next Due</th>
                        <th>Veterinarian</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($selectedPatientVaccinations as $record)
                        <tr>
                            <td>
                                <strong>
                                    {{ $vaccineLabels[$record->vaccine_key] ?? $record->vaccine_key }}
                                </strong>
                            </td>

                            <td>{{ $record->category }}</td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($record->date_given)->format('M d, Y') }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($record->next_due_date)->format('M d, Y') }}
                            </td>

                            <td>
                                {{ $record->veterinarian_name ?: 'Not specified' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="vet-empty-state">
                                No vaccination records available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

@endif

@endsection

@push('styles')
<style>
    .vet-search-form {
        display: flex;
        gap: 10px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .vet-input {
        flex: 1;
        min-width: 220px;
        padding: 12px 15px;
        border: 1px solid #e2dded;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
    }

    .vet-input:focus {
        border-color: #7652b5;
    }

    .vet-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        background: #7652b5;
        color: white;
        border: none;
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .vet-button:hover {
        background: #62409e;
        color: white;
    }

    .vet-button-light {
        background: #f1edf8;
        color: #6947a5;
    }

    .vet-button-light:hover {
        background: #e7def4;
        color: #56358f;
    }

    .vet-button-small {
        padding: 8px 12px;
        font-size: 12px;
    }

    .vet-badge {
        background: #f1edf8;
        color: #6947a5;
        padding: 7px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .vet-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .vet-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px;
    }

    .vet-table th {
        padding: 14px;
        text-align: left;
        background: #f8f6fb;
        color: #625875;
        font-size: 12px;
        font-weight: 700;
    }

    .vet-table td {
        padding: 14px;
        border-bottom: 1px solid #eeeaf4;
        font-size: 13px;
        color: #514960;
    }

    .vet-patient-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .vet-patient-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
    }

    .vet-patient-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eee7f8;
        color: #7652b5;
        font-weight: 700;
    }

    .vet-empty-state {
        text-align: center;
        padding: 30px !important;
        color: #93899f !important;
    }

    .vet-pagination {
        margin-top: 20px;
    }

    .vet-patient-details {
        margin-top: 24px;
    }

    .vet-patient-profile {
        display: flex;
        align-items: center;
        gap: 18px;
        margin: 20px 0;
    }

    .vet-patient-profile-image {
        width: 85px;
        height: 85px;
        border-radius: 16px;
        object-fit: cover;
    }

    .vet-patient-profile h2 {
        margin: 0 0 6px;
        color: #342a45;
    }

    .vet-patient-profile p {
        margin: 0;
        color: #887d98;
        font-size: 13px;
    }

    .vet-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .vet-detail-item {
        background: #faf9fc;
        border: 1px solid #eeeaf4;
        padding: 15px;
        border-radius: 10px;
        min-width: 0;
    }

    .vet-detail-item span {
        display: block;
        color: #91869f;
        font-size: 12px;
        margin-bottom: 7px;
    }

    .vet-detail-item strong {
        display: block;
        color: #453951;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .vet-alert {
        padding: 13px 16px;
        border-radius: 10px;
        margin-bottom: 18px;
    }

    .vet-alert-success {
        background: #eaf7ef;
        color: #287447;
    }

    @media (max-width: 768px) {
        .vet-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .vet-detail-grid {
            grid-template-columns: 1fr;
        }

        .vet-patient-profile {
            align-items: flex-start;
        }
    }
</style>
@endpush