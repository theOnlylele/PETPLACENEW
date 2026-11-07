
@extends('owner.layout')

@section('title', 'Add Pet')

@section('content')
<div class="page-heading">
    <h1>Add a New Pet</h1>
    <p>Register your pet in PetPlace.</p>
</div>

<div class="card">
    <form action="{{ route('owner.pets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="pet_name">Pet Name</label>
            <input
                type="text"
                name="pet_name"
                id="pet_name"
                class="form-control"
                value="{{ old('pet_name') }}"
                placeholder="Enter your pet's name"
                required
            >
        </div>

        <div class="form-group">
            <label for="pet_type">Pet Type</label>
            <select name="pet_type" id="pet_type" class="form-control" required>
                <option value="">Select pet type</option>
                <option value="Dog" {{ old('pet_type') == 'Dog' ? 'selected' : '' }}>Dog</option>
                <option value="Cat" {{ old('pet_type') == 'Cat' ? 'selected' : '' }}>Cat</option>
                <option value="Other" {{ old('pet_type') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="form-group" id="other-type-group" style="display:{{ old('pet_type') == 'Other' ? 'block' : 'none' }};">
            <label for="pet_type_other">Specify Pet Type</label>
            <input
                type="text"
                name="pet_type_other"
                id="pet_type_other"
                class="form-control"
                value="{{ old('pet_type_other') }}"
                placeholder="Enter pet type"
            >
        </div>

        <div class="form-group">
            <label for="pet_birthdate">Pet Birthdate</label>
            <input
                type="date"
                name="pet_birthdate"
                id="pet_birthdate"
                class="form-control"
                value="{{ old('pet_birthdate') }}"
                max="{{ date('Y-m-d') }}"
            >
        </div>

        <div class="form-group">
            <label for="pet_photo">Pet Photo</label>
            <input
                type="file"
                name="pet_photo"
                id="pet_photo"
                class="form-control"
                accept=".jpg,.jpeg,.png,.webp"
            >
        </div>

        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <button type="submit" class="btn">Save Pet</button>
            <a href="{{ route('owner.pets') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    const petType = document.getElementById('pet_type');
    const otherTypeGroup = document.getElementById('other-type-group');

    petType.addEventListener('change', function () {
        otherTypeGroup.style.display = this.value === 'Other' ? 'block' : 'none';
    });
</script>
@endsection
