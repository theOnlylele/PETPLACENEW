
@extends('owner.layout')

@section('title', 'Appointments')

@section('content')

@php
    $petType = strtolower($pet->pet_type ?? '');
    $isDogOrCat = in_array($petType, ['dog', 'cat']);
@endphp

<div class="page-heading">
    <h1>Appointments</h1>
    <p>Request veterinary services and manage your appointments.</p>
</div>

{{-- REQUEST APPOINTMENT --}}
<div class="card">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:22px;">
        <div style="background:#f0eaff; color:#7652c8; width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px;">
            +
        </div>
        <div>
            <h2 style="margin:0;">Request an appointment</h2>
            <p style="font-size:13px; color:#81798e; margin-top:4px;">
                Choose a service and preferred time for {{ $pet->pet_name }}.
            </p>
        </div>
    </div>

    @if ($pets->isEmpty())
        <p>No registered pets found. Please add a pet first.</p>
        <a href="{{ route('owner.pets.create') }}" class="btn">Add Pet</a>
    @else

    <form method="POST" action="{{ route('owner.appointments.store') }}">
        @csrf

        {{-- PET --}}
        <div class="form-group">
            <label for="pet_id">Select Pet</label>
            <select name="pet_id" id="pet_id" class="form-control" required>
                @foreach ($pets as $listedPet)
                    <option value="{{ $listedPet->id }}"
                        {{ old('pet_id', $pet->id) == $listedPet->id ? 'selected' : '' }}>
                        {{ $listedPet->pet_name }} ({{ $listedPet->pet_type }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SERVICE TYPE --}}
        <div class="form-group">
            <label for="service_type">Service type</label>
            <select name="service_type" id="service_type" class="form-control" required>
                <option value="Check-up / Consultation"
                    {{ old('service_type') == 'Check-up / Consultation' ? 'selected' : '' }}>
                    Check-up / Consultation
                </option>

                <option value="Vaccination"
                    {{ old('service_type') == 'Vaccination' ? 'selected' : '' }}>
                    Vaccination
                </option>

                <option value="Grooming"
                    {{ old('service_type') == 'Grooming' ? 'selected' : '' }}>
                    Grooming
                </option>
            </select>
        </div>

        {{-- VETERINARIAN --}}
        <div class="form-group" id="vet-field">
            <label for="vet_id">Veterinarian</label>
            <select name="vet_id" id="vet_id" class="form-control">
                <option value="">Any available veterinarian</option>

                @foreach ($vets as $vet)
                    <option value="{{ $vet->id }}"
                        {{ old('vet_id') == $vet->id ? 'selected' : '' }}>
                        {{ $vet->first_name }} {{ $vet->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- GROOMER --}}
        <div class="form-group" id="groomer-field" hidden>
            <label for="groomer_id">Groomer</label>
            <select name="groomer_id" id="groomer_id" class="form-control" disabled>
                <option value="">Any available groomer</option>

                @foreach ($groomers as $groomer)
                    <option value="{{ $groomer->id }}"
                        {{ old('groomer_id') == $groomer->id ? 'selected' : '' }}>
                        {{ $groomer->first_name }} {{ $groomer->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- VACCINATION OPTIONS --}}
        @if ($isDogOrCat)
        <fieldset id="vaccines-field" class="card" style="background:#faf9ff; margin-bottom:20px;" hidden disabled>
            <legend style="font-weight:700; color:#392d54; padding:0 5px;">
                Select Vaccines
            </legend>

            <p style="font-size:13px; color:#81798e; margin-bottom:15px;">
                Select the vaccines you want to request. Your veterinarian will confirm suitability.
            </p>

            @foreach ($vaccineOptions as $key => $option)
                <label style="display:flex; align-items:flex-start; gap:10px; margin-bottom:15px; cursor:pointer;">
                    <input
                        type="checkbox"
                        name="vaccines[]"
                        value="{{ $key }}"
                        id="vaccine-{{ $key }}"
                        style="margin-top:4px;"
                        {{ in_array($key, old('vaccines', [])) ? 'checked' : '' }}
                    >

                    <span>
                        <strong>{{ $option[0] }}</strong>
                        <small style="display:block; color:#81798e; margin-top:3px;">
                            {{ $option[1] }}
                        </small>
                        <small style="display:block; color:#7652c8;">
                            {{ $vaccineCategories[$key] ?? '' }}
                        </small>
                    </span>
                </label>
            @endforeach
        </fieldset>
        @endif

        {{-- GROOMING OPTIONS --}}
        <fieldset id="grooming-fields" class="card" style="background:#faf9ff; margin-bottom:20px;" hidden disabled>
            <legend style="font-weight:700; color:#392d54; padding:0 5px;">
                Grooming Details
            </legend>

            <div class="form-group">
                <label>Additional Services</label>

                @php
                    $groomingAddons = [
                        'nail_trimming' => 'Nail Trimming',
                        'ear_cleaning' => 'Ear Cleaning',
                        'teeth_brushing' => 'Teeth Brushing',
                        'anal_gland_expression' => 'Anal Gland Expression',
                        'flea_tick_treatment' => 'Flea and Tick Treatment',
                        'de_shedding' => 'De-shedding',
                        'medicated_shampoo' => 'Medicated Shampoo',
                        'perfume_bow_bandana' => 'Perfume / Bow / Bandana',
                    ];
                @endphp

                @foreach ($groomingAddons as $key => $label)
                    <label style="display:block; margin-bottom:10px;">
                        <input type="checkbox" name="grooming_addons[]" value="{{ $key }}"
                            {{ in_array($key, old('grooming_addons', [])) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <div class="form-group">
                <label for="haircut_style">Haircut Style</label>
                <select name="haircut_style" id="haircut_style" class="form-control">
                    <option value="">Select haircut style</option>
                    @foreach (['Short', 'Medium', 'Long', 'Same as last time', 'Let the groomer decide'] as $choice)
                        <option value="{{ $choice }}" {{ old('haircut_style') == $choice ? 'selected' : '' }}>
                            {{ $choice }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="shampoo_type">Shampoo Type</label>
                <select name="shampoo_type" id="shampoo_type" class="form-control">
                    <option value="">Select shampoo</option>
                    @foreach (['Regular', 'Hypoallergenic', 'Medicated'] as $choice)
                        <option value="{{ $choice }}" {{ old('shampoo_type') == $choice ? 'selected' : '' }}>
                            {{ $choice }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Is your pet's fur matted?</label>
                <select name="fur_matted" id="fur_matted" class="form-control">
                    <option value="">Select answer</option>
                    <option value="0" {{ old('fur_matted') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('fur_matted') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="pet_size">Pet Size</label>
                <select name="pet_size" id="pet_size" class="form-control">
                    <option value="">Select size</option>
                    @foreach (['Small', 'Medium', 'Large', 'Giant'] as $choice)
                        <option value="{{ $choice }}" {{ old('pet_size') == $choice ? 'selected' : '' }}>
                            {{ $choice }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="coat_type">Coat Type</label>
                <select name="coat_type" id="coat_type" class="form-control">
                    <option value="">Select coat type</option>
                    @foreach (['Short', 'Long', 'Curly', 'Double coat'] as $choice)
                        <option value="{{ $choice }}" {{ old('coat_type') == $choice ? 'selected' : '' }}>
                            {{ $choice }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="temperament">Pet Temperament</label>
                <select name="temperament" id="temperament" class="form-control">
                    <option value="">Select temperament</option>
                    @foreach (['Fine around strangers', 'Nervous', 'Aggressive'] as $choice)
                        <option value="{{ $choice }}" {{ old('temperament') == $choice ? 'selected' : '' }}>
                            {{ $choice }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="allergies_or_skin_problems">Allergies or Skin Problems</label>
                <textarea name="allergies_or_skin_problems" id="allergies_or_skin_problems"
                    class="form-control" rows="3"
                    placeholder="Describe any allergies or skin problems">{{ old('allergies_or_skin_problems') }}</textarea>
            </div>
        </fieldset>

        {{-- DATE AND TIME --}}
        <div class="form-group">
            <label for="appointment_at">Preferred date and time</label>
            <input
                type="datetime-local"
                name="appointment_at"
                id="appointment_at"
                class="form-control"
                value="{{ old('appointment_at') }}"
                min="{{ now()->format('Y-m-d\TH:i') }}"
                required
            >
        </div>

        {{-- REASON --}}
        <div class="form-group">
            <label for="reason">
                What does your pet need?
                <span style="font-weight:400; color:#81798e;">(optional)</span>
            </label>

            <textarea
                name="reason"
                id="reason"
                class="form-control"
                rows="3"
                maxlength="1000"
                placeholder="Anything you'd like the vet or groomer to know...">{{ old('reason') }}</textarea>
        </div>

        <button type="submit" class="btn" style="width:100%;">
            Request appointment
        </button>
    </form>
    @endif
</div>

{{-- APPOINTMENT HISTORY --}}
<div class="page-heading" style="margin-top:35px;">
    <h1>Appointment History</h1>
    <p>View your previous and upcoming appointment requests.</p>
</div>

@if ($appointments->isEmpty())
    <div class="card" style="text-align:center; padding:35px 20px;">
        <h2>No appointments yet</h2>
        <p style="color:#81798e;">Your appointment requests will appear here.</p>
    </div>
@else
    @foreach ($appointments as $appointment)
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <div>
                    <h2 style="margin-bottom:7px;">
                        {{ $appointment->service_type }}
                    </h2>

                    <p style="color:#81798e; font-size:14px;">
                        Pet: {{ $appointment->pet->pet_name ?? 'Unknown pet' }}
                    </p>
                </div>

                <span style="background:#f0eaff; color:#7652c8; padding:7px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                    {{ ucfirst($appointment->status ?? 'Pending') }}
                </span>
            </div>

            <div style="margin-top:17px; line-height:1.9; color:#655d75; font-size:14px;">
                <p>
                    <strong>Date:</strong>
                    {{ $appointment->appointment_at
                        ? \Illuminate\Support\Carbon::parse($appointment->appointment_at)->format('F d, Y')
                        : 'Not specified' }}
                </p>

                <p>
                    <strong>Time:</strong>
                    {{ $appointment->appointment_at
                        ? \Illuminate\Support\Carbon::parse($appointment->appointment_at)->format('h:i A')
                        : 'Not specified' }}
                </p>

                @if ($appointment->vet)
                    <p><strong>Veterinarian:</strong>
                        {{ $appointment->vet->first_name }} {{ $appointment->vet->last_name }}
                    </p>
                @endif

                @if ($appointment->groomer)
                    <p><strong>Groomer:</strong>
                        {{ $appointment->groomer->first_name }} {{ $appointment->groomer->last_name }}
                    </p>
                @endif

                @if ($appointment->reason)
                    <p><strong>Reason:</strong> {{ $appointment->reason }}</p>
                @endif
            </div>
        </div>
    @endforeach
@endif

@endsection

@push('scripts')
<script>
    const serviceType = document.getElementById('service_type');
    const vetField = document.getElementById('vet-field');
    const vetSelect = document.getElementById('vet_id');
    const groomerField = document.getElementById('groomer-field');
    const groomerSelect = document.getElementById('groomer_id');
    const vaccinesField = document.getElementById('vaccines-field');
    const groomingFields = document.getElementById('grooming-fields');

    function toggleStaffDropdown() {
        if (!serviceType) return;

        const isGrooming = serviceType.value === 'Grooming';
        const isVaccination = serviceType.value === 'Vaccination';

        vetField.hidden = isGrooming;
        vetSelect.disabled = isGrooming;

        groomerField.hidden = !isGrooming;
        groomerSelect.disabled = !isGrooming;

        groomingFields.hidden = !isGrooming;
        groomingFields.disabled = !isGrooming;

        if (vaccinesField) {
            vaccinesField.hidden = !isVaccination;
            vaccinesField.disabled = !isVaccination;
        }
    }

    if (serviceType) {
        serviceType.addEventListener('change', toggleStaffDropdown);
        toggleStaffDropdown();
    }

    const fvrcpCombo = document.getElementById('vaccine-cat_fvrcp');

    const fvrcpComponents = [
        'cat_rhinotracheitis',
        'cat_calicivirus',
        'cat_panleukopenia'
    ].map(vaccine => document.getElementById(`vaccine-${vaccine}`))
     .filter(Boolean);

    if (fvrcpCombo) {
        const allComponentsSelected = () =>
            fvrcpComponents.length > 0 &&
            fvrcpComponents.every(input => input.checked);

        const syncFvrcpCombo = () => {
            fvrcpCombo.checked = allComponentsSelected();
        };

        fvrcpCombo.addEventListener('change', () => {
            fvrcpComponents.forEach(input => {
                input.checked = fvrcpCombo.checked;
            });
        });

        fvrcpComponents.forEach(input => {
            input.addEventListener('change', syncFvrcpCombo);
        });

        if (fvrcpCombo.checked) {
            fvrcpComponents.forEach(input => input.checked = true);
        }

        syncFvrcpCombo();
    }
</script>
@endpush
