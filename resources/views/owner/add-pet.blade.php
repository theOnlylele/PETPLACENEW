
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
                <option value="dog" {{ old('pet_type') == 'dog' ? 'selected' : '' }}>Dog</option>
                <option value="cat" {{ old('pet_type') == 'cat' ? 'selected' : '' }}>Cat</option>
                <option value="rabbit" {{ old('pet_type') == 'rabbit' ? 'selected' : '' }}>Rabbit</option>
                <option value="fish" {{ old('pet_type') == 'fish' ? 'selected' : '' }}>Fish</option>
                <option value="bird" {{ old('pet_type') == 'bird' ? 'selected' : '' }}>Bird</option>
                <option value="reptile" {{ old('pet_type') == 'reptile' ? 'selected' : '' }}>Reptile</option>
                <option value="other" {{ old('pet_type') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="form-group" id="other-type-group" style="display:{{ old('pet_type') == 'other' ? 'block' : 'none' }};">
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
            <label for="breed">Breed</label>
            <input
                type="text"
                name="breed"
                id="breed"
                class="form-control"
                value="{{ old('breed') }}"
                placeholder="e.g. Golden Retriever, Persian, Lovebird"
            >
        </div>

        <div class="form-group">
            <label for="weight">Weight (kg)</label>
            <input
                type="number"
                name="weight"
                id="weight"
                class="form-control"
                value="{{ old('weight') }}"
                placeholder="e.g. 5.5"
                min="0.01"
                max="99999.99"
                step="0.01"
            >
        </div>

        <div class="form-group">
            <label for="allergies">Allergies</label>
            <textarea
                name="allergies"
                id="allergies"
                class="form-control"
                rows="3"
                placeholder="List known allergies, or leave blank if none are known"
            >{{ old('allergies') }}</textarea>
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
        otherTypeGroup.style.display = this.value === 'other' ? 'block' : 'none';
    });
</script>
@endsection
