@extends('vet.layout')

@section('title', 'Vaccinations')
@section('page-title', 'Vaccinations')

@section('content')

@php
    $today = today();
@endphp

<style>
    .vaccination-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .vaccination-heading h1 {
        font-size: 25px;
        font-weight: 750;
        color: #29213f;
        margin: 0 0 6px;
    }

    .vaccination-heading p {
        color: #827b91;
        margin: 0;
        font-size: 14px;
    }

    .vaccination-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
    }

    .vaccination-stat,
    .vaccination-card {
        background: #fff;
        border: 1px solid #eeeaf5;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 3px 12px rgba(45, 30, 75, .035);
    }

    .vaccination-stat span {
        display: block;
        color: #827b91;
        font-size: 13px;
        margin-bottom: 10px;
    }

    .vaccination-stat strong {
        font-size: 27px;
        color: #34264f;
    }

    .vaccination-card h2 {
        font-size: 17px;
        color: #34264f;
        margin: 0 0 5px;
    }

    .vaccination-muted {
        color: #8b8499;
        font-size: 13px;
        margin: 0 0 18px;
    }

    .vaccination-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .vaccination-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .vaccination-field.full {
        grid-column: 1 / -1;
    }

    .vaccination-field label {
        color: #514766;
        font-size: 13px;
        font-weight: 650;
    }

    .vaccination-field input,
    .vaccination-field select,
    .vaccination-field textarea,
    .vaccination-filter input,
    .vaccination-filter select {
        width: 100%;
        border: 1px solid #e6e0ef;
        border-radius: 9px;
        padding: 11px 12px;
        font-size: 13px;
        color: #34264f;
        background: #fff;
        outline: none;
    }

    .vaccination-field input:focus,
    .vaccination-field select:focus,
    .vaccination-field textarea:focus {
        border-color: #9471d5;
        box-shadow: 0 0 0 3px rgba(148, 113, 213, .12);
    }

    .vaccination-field textarea {
        resize: vertical;
        min-height: 85px;
    }

    .vaccination-primary-btn {
        border: none;
        border-radius: 9px;
        background: #7652b5;
        color: white;
        padding: 11px 18px;
        font-weight: 650;
        cursor: pointer;
        font-size: 13px;
    }

    .vaccination-primary-btn:hover {
        background: #62409e;
    }

    .vaccination-filter {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
    }

    .vaccination-filter input {
        flex: 1;
    }

    .vaccination-filter select {
        max-width: 180px;
    }

    .vaccination-table-wrap {
        overflow-x: auto;
    }

    .vaccination-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .vaccination-table th {
        text-align: left;
        padding: 12px 10px;
        font-size: 11px;
        color: #8b8499;
        text-transform: uppercase;
        letter-spacing: .4px;
        border-bottom: 1px solid #eeeaf5;
    }

    .vaccination-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #f1eef6;
        color: #514766;
        font-size: 13px;
    }

    .vaccination-table tr:last-child td {
        border-bottom: none;
    }

    .vaccination-pet-name {
        font-weight: 700;
        color: #34264f;
    }

    .vaccination-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-overdue {
        background: #fff0f0;
        color: #c34c4c;
    }

    .status-due {
        background: #fff6e7;
        color: #b77a20;
    }

    .status-upcoming {
        background: #edf8f1;
        color: #36865a;
    }

    .status-none {
        background: #f0edf5;
        color: #766b88;
    }

    .vaccination-certificate {
        color: #7652b5;
        font-weight: 650;
        text-decoration: none;
    }

    .vaccination-certificate:hover {
        text-decoration: underline;
    }

    .vaccination-empty {
        text-align: center;
        padding: 35px 15px;
        color: #8b8499;
        font-size: 13px;
    }

    .vaccination-pagination {
        margin-top: 18px;
    }

    .vaccination-errors {
        background: #fff0f0;
        color: #b83d3d;
        border: 1px solid #f5cccc;
        border-radius: 10px;
        padding: 13px 16px;
        font-size: 13px;
    }

    .vaccination-success {
        background: #edf8f1;
        color: #36865a;
        border: 1px solid #ccebd7;
        border-radius: 10px;
        padding: 13px 16px;
        font-size: 13px;
    }

    @media (max-width: 900px) {
        .vaccination-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .vaccination-form-grid {
            grid-template-columns: 1fr;
        }

        .vaccination-field.full {
            grid-column: auto;
        }

        .vaccination-filter {
            flex-direction: column;
        }

        .vaccination-filter select {
            max-width: none;
        }

        .vaccination-stat,
        .vaccination-card {
            padding: 15px;
        }
    }
</style>

<div class="vaccination-page">

    <div class="vaccination-heading">
        <h1>Vaccination Management</h1>
        <p>Record vaccinations and monitor your patients' upcoming vaccine schedules.</p>
    </div>

    @if (session('success'))
        <div class="vaccination-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="vaccination-errors">
            <strong>Please check the following:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- VACCINATION STATISTICS --}}
    <div class="vaccination-stats">

        <div class="vaccination-stat">
            <span>Total Records</span>
            <strong>{{ $vaccinationStats['total'] ?? 0 }}</strong>
        </div>

        <div class="vaccination-stat">
            <span>Overdue</span>
            <strong style="color: #c34c4c;">
                {{ $vaccinationStats['overdue'] ?? 0 }}
            </strong>
        </div>

        <div class="vaccination-stat">
            <span>Due Within 30 Days</span>
            <strong style="color: #b77a20;">
                {{ $vaccinationStats['due_soon'] ?? 0 }}
            </strong>
        </div>

        <div class="vaccination-stat">
            <span>Upcoming</span>
            <strong style="color: #36865a;">
                {{ $vaccinationStats['upcoming'] ?? 0 }}
            </strong>
        </div>

    </div>

    {{-- ADD VACCINATION FORM --}}
    <section class="vaccination-card">

        <h2>Record a Vaccination</h2>
        <p class="vaccination-muted">
            Enter the vaccination details for one of your patients.
        </p>

        <form action="{{ route('vet.vaccinations.store') }}" method="POST">
            @csrf

            <div class="vaccination-form-grid">

                <div class="vaccination-field full">
                    <label for="pet_id">Select Patient *</label>

                    <select name="pet_id" id="pet_id" required>
                        <option value="">Choose a patient</option>

                        @foreach ($patients as $pet)
                            <option value="{{ $pet->id }}"
                                {{ old('pet_id') == $pet->id ? 'selected' : '' }}>
                                {{ $pet->name ?? $pet->pet_name ?? 'Unnamed Pet' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="vaccination-field">
                    <label for="vaccine_key">Vaccine *</label>

                    <select name="vaccine_key" id="vaccine_key" required>
                        <option value="">Select vaccine</option>

                        @foreach ($vaccineLabels as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('vaccine_key') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach

                        <option value="other"
                            {{ old('vaccine_key') == 'other' ? 'selected' : '' }}>
                            Other / Custom Vaccine
                        </option>
                    </select>
                </div>

                <div class="vaccination-field" id="custom-vaccine-field"
                    style="{{ old('vaccine_key') == 'other' ? '' : 'display:none;' }}">
                    <label for="vaccine_name">Custom Vaccine Name</label>
                    <input
                        type="text"
                        name="vaccine_name"
                        id="vaccine_name"
                        value="{{ old('vaccine_name') }}"
                        placeholder="Enter vaccine name">
                </div>

                <div class="vaccination-field">
                    <label for="category">Category *</label>

                    <select name="category" id="category" required>
                        <option value="">Select category</option>
                        <option value="core" {{ old('category') == 'core' ? 'selected' : '' }}>
                            Core
                        </option>
                        <option value="non-core" {{ old('category') == 'non-core' ? 'selected' : '' }}>
                            Non-core / Lifestyle
                        </option>
                    </select>
                </div>

                <div class="vaccination-field">
                    <label for="date_given">Date Given *</label>
                    <input
                        type="date"
                        name="date_given"
                        id="date_given"
                        value="{{ old('date_given') }}"
                        max="{{ $today->format('Y-m-d') }}"
                        required>
                </div>

                <div class="vaccination-field">
                    <label for="next_due_date">Next Due Date</label>
                    <input
                        type="date"
                        name="next_due_date"
                        id="next_due_date"
                        value="{{ old('next_due_date') }}">
                </div>

                <div class="vaccination-field">
                    <label for="dose_number">Dose Number</label>
                    <input
                        type="number"
                        name="dose_number"
                        id="dose_number"
                        min="1"
                        value="{{ old('dose_number') }}"
                        placeholder="Example: 1">
                </div>

                <div class="vaccination-field">
                    <label for="brand">Vaccine Brand</label>
                    <input
                        type="text"
                        name="brand"
                        id="brand"
                        value="{{ old('brand') }}"
                        placeholder="Enter vaccine brand">
                </div>

                <div class="vaccination-field">
                    <label for="batch_number">Batch Number</label>
                    <input
                        type="text"
                        name="batch_number"
                        id="batch_number"
                        value="{{ old('batch_number') }}"
                        placeholder="Enter batch number">
                </div>

                <div class="vaccination-field full">
                    <label for="notes">Notes</label>
                    <textarea
                        name="notes"
                        id="notes"
                        placeholder="Additional vaccination notes...">{{ old('notes') }}</textarea>
                </div>

                <div class="vaccination-field full">
                    <button type="submit" class="vaccination-primary-btn">
                        Save Vaccination Record
                    </button>
                </div>

            </div>
        </form>

    </section>

    {{-- VACCINATION RECORDS --}}
    <section class="vaccination-card">

        <h2>Vaccination Records</h2>
        <p class="vaccination-muted">
            View and monitor the vaccination records of your patients.
        </p>

        <form action="{{ route('vet.vaccinations') }}" method="GET"
            class="vaccination-filter">

            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search pet, owner, or vaccine...">

            <select name="filter">
                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>
                    All Records
                </option>
                <option value="overdue" {{ $filter == 'overdue' ? 'selected' : '' }}>
                    Overdue
                </option>
                <option value="due_soon" {{ $filter == 'due_soon' ? 'selected' : '' }}>
                    Due Within 30 Days
                </option>
                <option value="upcoming" {{ $filter == 'upcoming' ? 'selected' : '' }}>
                    Upcoming
                </option>
            </select>

            <button type="submit" class="vaccination-primary-btn">
                Search
            </button>

        </form>

        <div class="vaccination-table-wrap">

            <table class="vaccination-table">

                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Vaccine</th>
                        <th>Date Given</th>
                        <th>Next Due</th>
                        <th>Status</th>
                        <th>Certificate</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($vaccinations as $record)

                        @php
                            $dueDate = $record->next_due_date
                                ? \Illuminate\Support\Carbon::parse($record->next_due_date)
                                : null;

                            if (!$dueDate) {
                                $status = 'No Due Date';
                                $statusClass = 'status-none';
                            } elseif ($dueDate->lt($today)) {
                                $status = 'Overdue';
                                $statusClass = 'status-overdue';
                            } elseif ($dueDate->lte($today->copy()->addDays(30))) {
                                $status = 'Due Soon';
                                $statusClass = 'status-due';
                            } else {
                                $status = 'Upcoming';
                                $statusClass = 'status-upcoming';
                            }

                            $petName = $record->pet->name
                                ?? $record->pet->pet_name
                                ?? 'Unknown Patient';

                            $vaccineName = $vaccineLabels[$record->vaccine_key]
                                ?? $record->vaccine_name
                                ?? $record->vaccine_key
                                ?? 'Unknown Vaccine';
                        @endphp

                        <tr>

                            <td>
                                <div class="vaccination-pet-name">
                                    {{ $petName }}
                                </div>
                                <div style="font-size: 11px; color: #938ba1; margin-top: 3px;">
                                    {{ $record->pet->petOwner->user->name ?? 'Owner' }}
                                </div>
                            </td>

                            <td>
                                {{ $vaccineName }}

                                @if ($record->brand)
                                    <div style="font-size: 11px; color: #938ba1; margin-top: 3px;">
                                        {{ $record->brand }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $record->date_given
                                    ? \Illuminate\Support\Carbon::parse($record->date_given)->format('M d, Y')
                                    : '—' }}
                            </td>

                            <td>
                                {{ $dueDate ? $dueDate->format('M d, Y') : '—' }}
                            </td>

                            <td>
                                <span class="vaccination-status {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('vet.vaccinations.certificate', $record) }}"
                                    class="vaccination-certificate"
                                    target="_blank">
                                    View Certificate
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="vaccination-empty">
                                No vaccination records found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="vaccination-pagination">
            {{ $vaccinations->links() }}
        </div>

    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const vaccineSelect = document.getElementById('vaccine_key');
        const customVaccineField = document.getElementById('custom-vaccine-field');
        const customVaccineInput = document.getElementById('vaccine_name');

        function toggleCustomVaccine() {
            const isOther = vaccineSelect.value === 'other';

            customVaccineField.style.display = isOther ? 'flex' : 'none';
            customVaccineInput.required = isOther;
        }

        vaccineSelect.addEventListener('change', toggleCustomVaccine);

        toggleCustomVaccine();
    });
</script>

@endsection
