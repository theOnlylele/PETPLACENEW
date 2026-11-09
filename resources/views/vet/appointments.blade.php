@extends('vet.layout')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')

<div class="appointments-page">

    <div class="vet-page-heading">
        <h1>Appointments</h1>
        <p>Review requests and manage your patient appointments.</p>
    </div>

    @if (session('status'))
        <div class="appointment-alert success-alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="appointment-alert error-alert">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PENDING REQUESTS --}}
    <section class="vet-card appointment-section">

        <div class="section-heading">
            <div>
                <h2>Pending Requests</h2>
                <p>Appointments waiting for your decision.</p>
            </div>

            <span class="count-badge">
                {{ $pendingRequests->count() }} Pending
            </span>
        </div>

        @forelse ($pendingRequests as $appointment)

            <div class="appointment-card">

                <div class="appointment-pet-icon">
                    {{ strtoupper(substr($appointment->pet?->pet_name ?? 'P', 0, 1)) }}
                </div>

                <div class="appointment-details">
                    <h3>{{ $appointment->pet?->pet_name ?? 'Pet' }}</h3>

                    <p class="appointment-owner">
                        {{ $appointment->service_type }}
                        <span>·</span>
                        {{ $appointment->petOwner?->first_name }}
                        {{ $appointment->petOwner?->last_name }}
                    </p>

                    <div class="appointment-date">
                        {{ $appointment->appointment_at?->format('M j, Y') ?? 'Date not set' }}
                        ·
                        {{ $appointment->appointment_at?->format('g:i A') ?? 'Time not set' }}
                    </div>
                </div>

                <div class="appointment-actions">

                    <form method="POST"
                          action="{{ route('vet.appointments.update', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="accept">

                        <button class="appointment-btn accept-btn" type="submit">
                            Accept
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('vet.appointments.update', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="decline">
                        <input type="hidden" name="decision_reason"
                               value="Appointment request declined by veterinarian.">

                        <button class="appointment-btn decline-btn" type="submit">
                            Decline
                        </button>
                    </form>

                </div>
            </div>

        @empty

            <div class="empty-state">
                <div class="empty-icon">✓</div>
                <h3>No pending requests</h3>
                <p>You're all caught up! New appointment requests will appear here.</p>
            </div>

        @endforelse

    </section>


    {{-- ALL APPOINTMENTS --}}
    <section class="vet-card appointment-section all-appointments">

        <div class="section-heading">
            <div>
                <h2>All Appointments</h2>
                <p>View appointments and record completed consultations or vaccinations.</p>
            </div>
        </div>

        {{-- FILTER --}}
        <form method="GET"
              action="{{ route('vet.appointments') }}"
              class="filter-form">

            <div class="filter-select-wrapper">
                <select name="status">
                    <option value="">All Statuses</option>

                    @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}"
                            @selected($selectedStatus === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="filter-btn">Filter</button>

            <a href="{{ route('vet.appointments') }}" class="reset-btn">
                Reset
            </a>

        </form>

        {{-- APPOINTMENT TABLE --}}
        <div class="appointment-table-wrapper">

            <table class="appointment-table">

                <thead>
                    <tr>
                        <th>Pet</th>
                        <th>Owner</th>
                        <th>Service</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($appointments as $appointment)

                        @php
                            $isVaccination = str_contains(
                                strtolower(trim($appointment->service_type ?? '')),
                                'vaccin'
                            );

                            $petType = strtolower(trim(
                                (string) ($appointment->pet?->pet_type ?? '')
                            ));

                            $availableVaccines = $vaccineOptionsByType[$petType] ?? [];

                            if (!is_array($availableVaccines)) {
                                $availableVaccines = [];
                            }

                            $oldVaccineKey = old('vaccine_key');
                        @endphp

                        <tr>

                            <td>
                                <div class="table-pet">
                                    <div class="table-pet-icon">
                                        {{ strtoupper(substr($appointment->pet?->pet_name ?? 'P', 0, 1)) }}
                                    </div>

                                    <strong>
                                        {{ $appointment->pet?->pet_name ?? 'Pet' }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                {{ trim(($appointment->petOwner?->first_name ?? '') . ' ' . ($appointment->petOwner?->last_name ?? '')) ?: 'Not available' }}
                            </td>

                            <td>
                                {{ $appointment->service_type ?? 'Not specified' }}
                            </td>

                            <td>
                                <div class="table-date">
                                    <strong>
                                        {{ $appointment->appointment_at?->format('M j, Y') ?? 'Not set' }}
                                    </strong>
                                    <small>
                                        {{ $appointment->appointment_at?->format('g:i A') ?? '' }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                <span class="status-badge status-{{ strtolower($appointment->status) }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>

                            <td>

                                @if ($appointment->status === 'confirmed' && $appointment->pet)

                                    <details class="completion-details">

                                        <summary class="complete-btn">
                                            Mark as Done
                                        </summary>

                                        <div class="completion-form-panel">

                                            @if ($isVaccination)

                                                {{-- VACCINATION COMPLETION FORM --}}

                                                <h3>Complete Vaccination</h3>

                                                <p>
                                                    Record the vaccine administered to
                                                    {{ $appointment->pet->pet_name }}
                                                    before completing this appointment.
                                                </p>

                                                <form method="POST"
                                                      action="{{ route('vet.vaccinations.store') }}">
                                                    @csrf

                                                    <input type="hidden"
                                                           name="pet_id"
                                                           value="{{ $appointment->pet_id }}">

                                                    <input type="hidden"
                                                           name="appointment_id"
                                                           value="{{ $appointment->id }}">

                                                    <div class="form-field">
                                                        <label>Vaccine *</label>

                                                        <select name="vaccine_key"
                                                                class="vaccine-select"
                                                                required>

                                                            <option value="">Select vaccine</option>

                                                            @foreach ($availableVaccines as $key => $label)
                                                                <option value="{{ $key }}"
                                                                    @selected($oldVaccineKey === $key)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach

                                                            <option value="other"
                                                                @selected($oldVaccineKey === 'other')>
                                                                Other
                                                            </option>

                                                        </select>

                                                        @if (empty($availableVaccines))
                                                            <small class="field-warning">
                                                                No predefined vaccines were found for this pet type.
                                                                You can select Other and enter the vaccine name.
                                                            </small>
                                                        @endif
                                                    </div>

                                                    {{-- ONLY REQUIRED WHEN OTHER IS SELECTED --}}
                                                    <div class="form-field other-vaccine-field"
                                                         style="{{ $oldVaccineKey === 'other' ? '' : 'display:none;' }}">

                                                        <label>Vaccine Name *</label>

                                                        <input type="text"
                                                               name="vaccine_name"
                                                               value="{{ old('vaccine_name') }}"
                                                               maxlength="100"
                                                               placeholder="Enter vaccine name"
                                                               {{ $oldVaccineKey === 'other' ? 'required' : '' }}>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Vaccine Category *</label>

                                                        <select name="category" required>
                                                            <option value="">Select category</option>

                                                            <option value="Core"
                                                                @selected(old('category') === 'Core')>
                                                                Core
                                                            </option>

                                                            <option value="Non-core (Lifestyle)"
                                                                @selected(old('category') === 'Non-core (Lifestyle)')>
                                                                Non-core (Lifestyle)
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Date Given *</label>

                                                        <input type="date"
                                                               name="date_given"
                                                               value="{{ old('date_given', now()->toDateString()) }}"
                                                               max="{{ now()->toDateString() }}"
                                                               required>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Next Due Date</label>

                                                        <input type="date"
                                                               name="next_due_date"
                                                               value="{{ old('next_due_date') }}"
                                                               min="{{ now()->toDateString() }}">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Dose Number</label>

                                                        <input type="text"
                                                               name="dose_number"
                                                               value="{{ old('dose_number') }}"
                                                               maxlength="50"
                                                               placeholder="Example: 1st dose">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Vaccine Brand</label>

                                                        <input type="text"
                                                               name="brand"
                                                               value="{{ old('brand') }}"
                                                               maxlength="255"
                                                               placeholder="Enter vaccine brand">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Batch Number</label>

                                                        <input type="text"
                                                               name="batch_number"
                                                               value="{{ old('batch_number') }}"
                                                               maxlength="255"
                                                               placeholder="Enter batch number">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Notes</label>

                                                        <textarea name="notes"
                                                                  rows="3"
                                                                  maxlength="1000"
                                                                  placeholder="Additional vaccination notes...">{{ old('notes') }}</textarea>
                                                    </div>

                                                    <div class="completion-actions">
                                                        <button type="submit"
                                                                class="appointment-btn save-vaccination-btn">
                                                            Save Vaccination & Complete
                                                        </button>
                                                    </div>

                                                </form>

                                            @else

                                                {{-- CONSULTATION COMPLETION FORM --}}

                                                <h3>Complete Consultation</h3>

                                                <p>
                                                    Record the patient's consultation before completing this appointment.
                                                </p>

                                                <form method="POST"
                                                      action="{{ route('vet.visits.store') }}">
                                                    @csrf

                                                    <input type="hidden"
                                                           name="pet_id"
                                                           value="{{ $appointment->pet_id }}">

                                                    <input type="hidden"
                                                           name="appointment_id"
                                                           value="{{ $appointment->id }}">

                                                    <div class="form-field">
                                                        <label>Visit Date *</label>

                                                        <input type="date"
                                                               name="visit_date"
                                                               value="{{ now()->toDateString() }}"
                                                               max="{{ now()->toDateString() }}"
                                                               required>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Reason for Visit</label>

                                                        <textarea name="reason"
                                                                  rows="2"
                                                                  maxlength="2000"
                                                                  placeholder="Describe the reason for the visit..."></textarea>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Diagnosis</label>

                                                        <textarea name="diagnosis"
                                                                  rows="3"
                                                                  maxlength="4000"
                                                                  placeholder="Enter the patient's diagnosis..."></textarea>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Treatment</label>

                                                        <textarea name="treatment"
                                                                  rows="3"
                                                                  maxlength="4000"
                                                                  placeholder="Enter the treatment provided..."></textarea>
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Follow-up Date</label>

                                                        <input type="date"
                                                               name="follow_up_date"
                                                               min="{{ now()->toDateString() }}">
                                                    </div>

                                                    <div class="form-divider"></div>

                                                    <h4>Prescription Details</h4>

                                                    <div class="form-field">
                                                        <label>Medicine</label>

                                                        <input type="text"
                                                               name="prescription_medicine"
                                                               maxlength="255"
                                                               placeholder="Medicine name">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Dosage</label>

                                                        <input type="text"
                                                               name="prescription_dosage"
                                                               maxlength="255"
                                                               placeholder="Example: 1 tablet">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Duration</label>

                                                        <input type="text"
                                                               name="prescription_duration"
                                                               maxlength="255"
                                                               placeholder="Example: 7 days">
                                                    </div>

                                                    <div class="form-field">
                                                        <label>Prescription Instructions</label>

                                                        <textarea name="prescription_instructions"
                                                                  rows="2"
                                                                  maxlength="1000"
                                                                  placeholder="Additional instructions..."></textarea>
                                                    </div>

                                                    <div class="completion-actions">
                                                        <button type="submit"
                                                                class="appointment-btn save-visit-btn">
                                                            Save History & Complete
                                                        </button>
                                                    </div>

                                                </form>

                                            @endif

                                        </div>
                                    </details>

                                @elseif ($appointment->status === 'completed')

                                    <span class="completed-label">
                                        <span>✓</span> Completed
                                    </span>

                                @else

                                    <span class="no-action">—</span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">
                                <div class="table-empty-state">
                                    <h3>No appointments found</h3>
                                    <p>There are no appointments matching your selected filter.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if ($appointments->hasPages())
            <div class="appointment-pagination">
                {{ $appointments->links() }}
            </div>
        @endif

        <div class="appointment-table-footer">
            Showing {{ $appointments->firstItem() ?? 0 }}
            to {{ $appointments->lastItem() ?? 0 }}
            of {{ $appointments->total() }} appointments
        </div>

    </section>

</div>


<style>

    .appointments-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        width: 100%;
        min-width: 0;
    }

    .appointments-page .vet-page-heading {
        margin-bottom: 0;
    }

    .appointments-page .vet-page-heading h1 {
        font-size: 27px;
        color: #302746;
        margin-bottom: 6px;
    }

    .appointments-page .vet-page-heading p {
        color: #81798e;
        font-size: 14px;
    }

    .appointment-section {
        padding: 25px;
        margin-bottom: 0;
        min-width: 0;
    }

    .section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 22px;
    }

    .section-heading h2 {
        font-size: 19px;
        color: #302746;
        margin: 0 0 5px;
    }

    .section-heading p {
        font-size: 13px;
        color: #81798e;
        margin: 0;
    }

    .count-badge {
        padding: 8px 13px;
        border-radius: 20px;
        background: #f0eaff;
        color: #7553b8;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* PENDING APPOINTMENTS */

    .appointment-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border: 1px solid #eeeaf5;
        border-radius: 12px;
        margin-bottom: 12px;
        background: #fff;
        transition: .2s;
    }

    .appointment-card:last-child {
        margin-bottom: 0;
    }

    .appointment-card:hover {
        border-color: #d8c9f2;
        box-shadow: 0 4px 14px rgba(70, 45, 120, .05);
    }

    .appointment-pet-icon,
    .table-pet-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #f0eaff;
        color: #7553b8;
        font-size: 19px;
        font-weight: 800;
    }

    .appointment-details {
        flex: 1;
        min-width: 0;
    }

    .appointment-details h3 {
        color: #302746;
        font-size: 15px;
        margin: 0 0 6px;
    }

    .appointment-owner {
        color: #81798e;
        font-size: 12px;
        margin-bottom: 9px;
    }

    .appointment-date {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        color: #655d75;
        font-size: 12px;
    }

    .appointment-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .appointment-actions form {
        margin: 0;
    }

    .appointment-btn {
        border: none;
        border-radius: 8px;
        padding: 10px 17px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
    }

    .accept-btn {
        background: #7553b8;
        color: white;
    }

    .accept-btn:hover {
        background: #60409f;
    }

    .decline-btn {
        background: #fff0f0;
        color: #b64d4d;
    }

    .decline-btn:hover {
        background: #ffe1e1;
    }

    /* EMPTY STATES */

    .empty-state {
        text-align: center;
        padding: 38px 20px;
        background: #faf9fc;
        border: 1px dashed #e5deef;
        border-radius: 12px;
    }

    .empty-icon {
        width: 45px;
        height: 45px;
        margin: 0 auto 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eaf8ef;
        color: #287341;
        font-size: 22px;
        font-weight: 800;
    }

    .empty-state h3 {
        color: #403653;
        font-size: 15px;
        margin-bottom: 6px;
    }

    .empty-state p {
        color: #81798e;
        font-size: 12px;
    }

    /* FILTER */

    .filter-form {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .filter-select-wrapper {
        min-width: 190px;
    }

    .filter-form select {
        width: 100%;
        min-height: 42px;
        padding: 9px 13px;
        border: 1px solid #ded9e8;
        border-radius: 8px;
        background: #fff;
        color: #403653;
        font-size: 13px;
        outline: none;
    }

    .filter-form select:focus {
        border-color: #8061c9;
        box-shadow: 0 0 0 3px rgba(128, 97, 201, .12);
    }

    .filter-btn,
    .reset-btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 8px;
        padding: 9px 17px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .filter-btn {
        background: #7553b8;
        color: white;
    }

    .filter-btn:hover {
        background: #60409f;
    }

    .reset-btn {
        background: #f0eaff;
        color: #7553b8;
    }

    .reset-btn:hover {
        background: #e5d9ff;
    }

    /* TABLE */

    .appointment-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #eeeaf5;
        border-radius: 10px;
    }

    .appointment-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 850px;
    }

    .appointment-table thead {
        background: #faf8fd;
    }

    .appointment-table th {
        padding: 15px 16px;
        color: #81798e;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .appointment-table td {
        padding: 15px 16px;
        border-top: 1px solid #f0edf5;
        color: #514960;
        font-size: 12px;
        vertical-align: middle;
    }

    .appointment-table tbody tr:hover {
        background: #fcfbfe;
    }

    .table-pet {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #302746;
    }

    .table-pet-icon {
        width: 35px;
        height: 35px;
        min-width: 35px;
        border-radius: 9px;
        font-size: 14px;
    }

    .table-date {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .table-date strong {
        color: #403653;
        font-size: 12px;
    }

    .table-date small {
        color: #81798e;
        font-size: 11px;
    }

    /* STATUS */

    .status-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff5df;
        color: #a56b0b;
    }

    .status-confirmed {
        background: #eee8ff;
        color: #6849a5;
    }

    .status-completed {
        background: #eaf8ef;
        color: #287341;
    }

    .status-cancelled {
        background: #fff0f0;
        color: #b64d4d;
    }

    /* COMPLETION FORM */

    .completion-details {
        min-width: 145px;
    }

    .complete-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 9px 13px;
        background: #eaf8ef;
        color: #287341;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        list-style: none;
        white-space: nowrap;
    }

    .complete-btn::-webkit-details-marker {
        display: none;
    }

    .complete-btn:hover {
        background: #d9f1e1;
    }

    .completion-details[open] .complete-btn {
        background: #287341;
        color: white;
    }

    .completion-form-panel {
        position: relative;
        width: 330px;
        max-width: calc(100vw - 50px);
        padding: 20px;
        margin-top: 10px;
        background: #fff;
        border: 1px solid #e9e3f2;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(48, 39, 70, .10);
        white-space: normal;
    }

    .completion-form-panel h3 {
        color: #302746;
        font-size: 16px;
        margin: 0 0 6px;
    }

    .completion-form-panel > p {
        color: #81798e;
        font-size: 11px;
        line-height: 1.5;
        margin-bottom: 17px;
    }

    .completion-form-panel h4 {
        color: #403653;
        font-size: 13px;
        margin: 0 0 13px;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 13px;
    }

    .form-field label {
        color: #514960;
        font-size: 11px;
        font-weight: 700;
    }

    .form-field input,
    .form-field textarea,
    .form-field select {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 11px;
        border: 1px solid #ded9e8;
        border-radius: 7px;
        background: #fff;
        color: #302746;
        font-family: inherit;
        font-size: 12px;
        outline: none;
    }

    .form-field input:focus,
    .form-field textarea:focus,
    .form-field select:focus {
        border-color: #8061c9;
        box-shadow: 0 0 0 3px rgba(128, 97, 201, .10);
    }

    .form-field textarea {
        resize: vertical;
    }

    .field-warning {
        color: #a56b0b;
        font-size: 10px;
        line-height: 1.5;
    }

    .form-divider {
        height: 1px;
        background: #eeeaf5;
        margin: 18px 0;
    }

    .completion-actions {
        margin-top: 17px;
    }

    .save-visit-btn,
    .save-vaccination-btn {
        width: 100%;
        padding: 12px;
        color: white;
    }

    .save-visit-btn {
        background: #7553b8;
    }

    .save-visit-btn:hover {
        background: #60409f;
    }

    .save-vaccination-btn {
        background: #287341;
    }

    .save-vaccination-btn:hover {
        background: #205d35;
    }

    .completed-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #287341;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .no-action {
        color: #aaa3b5;
    }

    /* TABLE EMPTY STATE */

    .table-empty-state {
        text-align: center;
        padding: 35px 15px;
    }

    .table-empty-state h3 {
        color: #403653;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .table-empty-state p {
        color: #81798e;
        font-size: 12px;
    }

    /* PAGINATION */

    .appointment-pagination {
        display: flex;
        justify-content: center;
        margin-top: 22px;
    }

    .appointment-pagination nav {
        display: flex;
        justify-content: center;
    }

    .appointment-pagination svg {
        width: 17px;
        height: 17px;
    }

    .appointment-table-footer {
        margin-top: 15px;
        color: #81798e;
        font-size: 11px;
        text-align: right;
    }

    /* ALERTS */

    .appointment-alert {
        padding: 14px 17px;
        border-radius: 9px;
        font-size: 13px;
    }

    .success-alert {
        background: #edf8f0;
        border: 1px solid #c9e8d0;
        color: #287341;
    }

    .error-alert {
        background: #fff0f0;
        border: 1px solid #f0cccc;
        color: #a43e3e;
    }

    .error-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    /* RESPONSIVE */

    @media (max-width: 800px) {
        .appointment-card {
            flex-wrap: wrap;
        }

        .appointment-actions {
            width: 100%;
            padding-left: 64px;
        }
    }

    @media (max-width: 600px) {
        .appointment-section {
            padding: 17px;
        }

        .section-heading {
            align-items: flex-start;
        }

        .appointment-card {
            gap: 12px;
        }

        .appointment-actions {
            padding-left: 0;
        }

        .appointment-actions form {
            flex: 1;
        }

        .appointment-btn {
            width: 100%;
        }

        .filter-form {
            align-items: stretch;
        }

        .filter-select-wrapper {
            width: 100%;
        }

        .filter-btn,
        .reset-btn {
            flex: 1;
        }

        .completion-form-panel {
            position: fixed;
            z-index: 1000;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: calc(100vw - 40px);
            max-height: 80vh;
            overflow-y: auto;
        }
    }

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.completion-form-panel').forEach(function (panel) {

        const vaccineSelect = panel.querySelector('.vaccine-select');
        const otherField = panel.querySelector('.other-vaccine-field');

        if (!vaccineSelect || !otherField) {
            return;
        }

        const vaccineNameInput = otherField.querySelector(
            'input[name="vaccine_name"]'
        );

        function toggleOtherVaccine() {

            const isOther = vaccineSelect.value === 'other';

            otherField.style.display = isOther ? 'flex' : 'none';

            vaccineNameInput.required = isOther;

            if (!isOther) {
                vaccineNameInput.value = '';
            }
        }

        vaccineSelect.addEventListener('change', toggleOtherVaccine);

        toggleOtherVaccine();

    });

});
</script>

@endsection