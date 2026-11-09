
@extends('vet.layout')

@section('title', 'Profile & Availability')
@section('page-title', 'Profile & Availability')

@section('content')

<div class="vet-profile-page">

    <div class="vet-page-heading">
        <h1>Profile & Availability</h1>
        <p>Manage your professional information and working schedule.</p>
    </div>

    @if (session('status'))
        <div class="profile-alert profile-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="profile-alert profile-error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PROFESSIONAL PROFILE --}}
    <section class="vet-card profile-section">

        <div class="profile-section-heading">
            <div>
                <h2>Professional Information</h2>
                <p class="vet-muted">Update your account and professional details.</p>
            </div>
        </div>

        <form action="{{ route('vet.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- PROFILE PHOTO --}}
            <div class="profile-photo-section">

                <div class="profile-photo-preview-wrapper">
                    <img
                        id="profile-photo-preview"
                        src="{{ $vet->profile_photo ? asset('storage/' . $vet->profile_photo) : '' }}"
                        alt="Vet Profile Photo"
                        class="profile-photo-preview"
                        style="{{ $vet->profile_photo ? '' : 'display: none;' }}"
                    >

                    <div
                        id="profile-photo-placeholder"
                        class="profile-photo-placeholder"
                        style="{{ $vet->profile_photo ? 'display: none;' : '' }}"
                    >
                        <span>{{ strtoupper(substr($vet->first_name ?? 'V', 0, 1)) }}</span>
                    </div>
                </div>

                <div class="profile-photo-controls">
                    <label for="profile_photo" class="profile-photo-upload-btn">
                        Choose Profile Photo
                    </label>

                    <input
                        type="file"
                        id="profile_photo"
                        name="profile_photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        hidden
                    >

                    <p>JPG, JPEG, PNG, or WEBP. Maximum file size: 2MB.</p>
                    <small id="profile-photo-filename">No new photo selected.</small>
                </div>

            </div>

            <div class="profile-grid">

                <div class="profile-field">
                    <label for="first_name">First Name</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="{{ old('first_name', $vet->first_name) }}"
                        required
                    >
                </div>

                <div class="profile-field">
                    <label for="last_name">Last Name</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="{{ old('last_name', $vet->last_name) }}"
                        required
                    >
                </div>

                <div class="profile-field">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                    >
                </div>

                <div class="profile-field">
                    <label for="phone">Contact Number</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $vet->phone) }}"
                        placeholder="Enter your contact number"
                    >
                </div>

                <div class="profile-field">
                    <label for="clinic_name">Clinic Name</label>
                    <input
                        type="text"
                        id="clinic_name"
                        name="clinic_name"
                        value="{{ old('clinic_name', $vet->clinic_name) }}"
                        placeholder="Enter clinic name"
                    >
                </div>

                <div class="profile-field">
                    <label for="specialty">Specialty</label>
                    <select id="specialty" name="specialty">
                        <option value="">Select specialty</option>
                        @foreach ([
                            'dog' => 'Dog',
                            'cat' => 'Cat',
                            'rabbit' => 'Rabbit',
                            'fish' => 'Fish',
                            'bird' => 'Bird',
                            'reptile' => 'Reptile'
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                {{ old('specialty', $vet->specialty) === $value ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="profile-field profile-full">
                    <label for="license_number">License Number</label>
                    <input
                        type="text"
                        id="license_number"
                        value="{{ $vet->license_number }}"
                        readonly
                    >
                    <small>Contact your administrator if your license information needs correction.</small>
                </div>

            </div>

            <div class="profile-actions">
                <button type="submit" class="profile-save-btn">
                    Save Profile
                </button>
            </div>

        </form>
    </section>


    {{-- AVAILABILITY --}}
    <section class="vet-card profile-section">

        <div class="profile-section-heading">
            <div>
                <h2>Working Availability</h2>
                <p class="vet-muted">Set the days and hours you are available for appointments.</p>
            </div>
        </div>

        <form action="{{ route('vet.availability.update') }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $weekdays = [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday'
                ];

                $selectedDays = old('working_days', $vet->working_days ?? []);
                $selectedDaysOff = old('days_off', $vet->days_off ?? []);
            @endphp

            <div class="profile-field">
                <label>Working Days</label>

                <div class="profile-days-grid">
                    @foreach ($weekdays as $day)
                        <label class="profile-day-option">
                            <input
                                type="checkbox"
                                name="working_days[]"
                                value="{{ $day }}"
                                {{ in_array($day, $selectedDays ?? []) ? 'checked' : '' }}
                            >
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="profile-grid profile-hours-grid">

                <div class="profile-field">
                    <label for="working_hours_start">Start Time</label>
                    <input
                        type="time"
                        id="working_hours_start"
                        name="working_hours_start"
                        value="{{ old('working_hours_start', $vet->working_hours_start ? substr($vet->working_hours_start, 0, 5) : '') }}"
                    >
                </div>

                <div class="profile-field">
                    <label for="working_hours_end">End Time</label>
                    <input
                        type="time"
                        id="working_hours_end"
                        name="working_hours_end"
                        value="{{ old('working_hours_end', $vet->working_hours_end ? substr($vet->working_hours_end, 0, 5) : '') }}"
                    >
                </div>

            </div>

            <div class="profile-field profile-days-off">
                <label>Days Off</label>
                <p class="vet-muted">Add dates when you will not be available.</p>

                <div id="days-off-container">

                    @forelse ($selectedDaysOff as $dayOff)
                        <div class="days-off-row">
                            <input
                                type="date"
                                name="days_off[]"
                                value="{{ $dayOff }}"
                            >
                            <button type="button" class="remove-day-btn" onclick="removeDayOff(this)">
                                Remove
                            </button>
                        </div>
                    @empty
                        <div class="days-off-row">
                            <input type="date" name="days_off[]">
                            <button type="button" class="remove-day-btn" onclick="removeDayOff(this)">
                                Remove
                            </button>
                        </div>
                    @endforelse

                </div>

                <button type="button" class="add-day-btn" onclick="addDayOff()">
                    + Add Another Day Off
                </button>
            </div>

            <div class="profile-actions">
                <button type="submit" class="profile-save-btn">
                    Save Availability
                </button>
            </div>

        </form>
    </section>

</div>


<style>
    .vet-profile-page {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .profile-section {
        padding: 26px;
    }

    .profile-section-heading {
        margin-bottom: 24px;
    }

    .profile-section-heading h2 {
        margin: 0 0 6px;
        font-size: 19px;
        font-weight: 700;
        color: #29213d;
    }

    .profile-section-heading p {
        margin: 0;
        font-size: 13px;
    }

    /* PROFILE PHOTO */
    .profile-photo-section {
        display: flex;
        align-items: center;
        gap: 22px;
        margin-bottom: 28px;
        padding: 20px;
        background: #faf8fd;
        border: 1px solid #ebe5f4;
        border-radius: 12px;
    }

    .profile-photo-preview-wrapper {
        width: 105px;
        height: 105px;
        min-width: 105px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e5dcf4;
        background: #eee8f7;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-photo-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #7553b8;
        color: white;
        font-size: 38px;
        font-weight: 700;
    }

    .profile-photo-controls {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .profile-photo-upload-btn {
        display: inline-block;
        padding: 10px 16px;
        border-radius: 8px;
        background: #eee8f7;
        color: #6849a5;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .profile-photo-upload-btn:hover {
        background: #e1d7f1;
    }

    .profile-photo-controls p,
    .profile-photo-controls small {
        margin: 0;
        color: #81798d;
        font-size: 11px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .profile-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .profile-field label {
        font-size: 13px;
        font-weight: 600;
        color: #403653;
    }

    .profile-field input,
    .profile-field select {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #ded9e8;
        border-radius: 8px;
        background: #fff;
        color: #30283e;
        font-size: 13px;
        box-sizing: border-box;
    }

    .profile-field input:focus,
    .profile-field select:focus {
        outline: none;
        border-color: #8061c9;
        box-shadow: 0 0 0 3px rgba(128, 97, 201, 0.12);
    }

    .profile-field input[readonly] {
        background: #f5f3f8;
        color: #777080;
        cursor: not-allowed;
    }

    .profile-field small {
        color: #81798d;
        font-size: 11px;
    }

    .profile-full {
        grid-column: 1 / -1;
    }

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }

    .profile-save-btn {
        border: none;
        border-radius: 8px;
        padding: 12px 22px;
        background: #7553b8;
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .profile-save-btn:hover {
        background: #60409f;
    }

    .profile-days-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .profile-day-option {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 12px;
        border: 1px solid #e3deeb;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        background: #fff;
    }

    .profile-day-option input {
        width: 16px;
        height: 16px;
        accent-color: #7553b8;
    }

    .profile-day-option span {
        color: #403653;
        font-weight: 500;
    }

    .profile-hours-grid {
        margin-top: 12px;
    }

    .profile-days-off {
        margin-top: 8px;
    }

    .profile-days-off p {
        margin: 0 0 8px;
        font-size: 12px;
    }

    .days-off-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .days-off-row input {
        max-width: 260px;
    }

    .remove-day-btn {
        border: 1px solid #ead1d1;
        background: #fff;
        color: #b64d4d;
        border-radius: 7px;
        padding: 10px 13px;
        font-size: 12px;
        cursor: pointer;
    }

    .add-day-btn {
        border: 1px dashed #a58bcf;
        background: #faf8fd;
        color: #7553b8;
        border-radius: 7px;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .profile-alert {
        padding: 14px 18px;
        border-radius: 8px;
        font-size: 13px;
    }

    .profile-success {
        background: #edf8f0;
        border: 1px solid #c9e8d0;
        color: #287341;
    }

    .profile-error {
        background: #fff0f0;
        border: 1px solid #f0cccc;
        color: #a43e3e;
    }

    .profile-error ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    @media (max-width: 700px) {
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .profile-full {
            grid-column: auto;
        }

        .profile-days-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .profile-section {
            padding: 18px;
        }

        .days-off-row {
            flex-wrap: wrap;
        }

        .profile-photo-section {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>


<script>
    // PROFILE PHOTO PREVIEW
    document.addEventListener('DOMContentLoaded', function () {
        const photoInput = document.getElementById('profile_photo');
        const photoPreview = document.getElementById('profile-photo-preview');
        const photoPlaceholder = document.getElementById('profile-photo-placeholder');
        const photoFilename = document.getElementById('profile-photo-filename');

        photoInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                alert('Please select a JPG, JPEG, PNG, or WEBP image.');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('The image must not be larger than 2MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                photoPreview.src = event.target.result;
                photoPreview.style.display = 'block';
                photoPlaceholder.style.display = 'none';
            };

            reader.readAsDataURL(file);

            photoFilename.textContent = file.name;
        });
    });

    // DAYS OFF
    function addDayOff() {
        const container = document.getElementById('days-off-container');

        const row = document.createElement('div');
        row.className = 'days-off-row';

        row.innerHTML = `
            <input type="date" name="days_off[]">
            <button type="button" class="remove-day-btn" onclick="removeDayOff(this)">
                Remove
            </button>
        `;

        container.appendChild(row);
    }

    function removeDayOff(button) {
        const container = document.getElementById('days-off-container');
        const row = button.closest('.days-off-row');

        row.remove();

        if (container.children.length === 0) {
            addDayOff();
        }
    }
</script>

@endsection
