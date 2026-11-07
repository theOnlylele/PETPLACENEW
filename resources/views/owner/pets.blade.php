
@extends('owner.layout')

@section('title', 'My Pets')

@section('content')
<div class="page-heading">
    <h1>My Pets</h1>
    <p>Manage your pets and keep their information updated.</p>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:25px;">
    <h2 style="color:#392d54;">Your Registered Pets</h2>
    <a href="{{ route('owner.pets.create') }}" class="btn">+ Add Pet</a>
</div>

@if ($pets->isEmpty())
    <div class="card" style="text-align:center; padding:45px 20px;">
        <h2>No pets registered yet</h2>
        <p style="color:#81798e; margin-bottom:20px;">Add your first pet to get started.</p>
        <a href="{{ route('owner.pets.create') }}" class="btn">Add Your First Pet</a>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:15px; margin-bottom:25px;">
        @foreach ($pets as $listedPet)
            <a href="{{ route('owner.pets', ['pet_id' => $listedPet->id]) }}"
               class="card"
               style="margin:0; text-align:center; border:2px solid {{ isset($pet) && $pet->id == $listedPet->id ? '#7652c8' : '#eeeaf5' }};">
                
                @if ($listedPet->pet_photo_path)
                    <img src="{{ asset('storage/' . $listedPet->pet_photo_path) }}"
                         alt="{{ $listedPet->pet_name }}"
                         style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:12px;">
                @else
                    <div style="width:100px; height:100px; border-radius:50%; background:#f0eaff; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:#7652c8; font-size:30px; font-weight:bold;">
                        {{ strtoupper(substr($listedPet->pet_name ?? 'P', 0, 1)) }}
                    </div>
                @endif

                <h3 style="color:#392d54;">{{ $listedPet->pet_name }}</h3>
                <p style="color:#81798e; font-size:13px;">{{ $listedPet->pet_type }}</p>
            </a>
        @endforeach
    </div>

    @if (isset($pet) && $pet)
        <div class="card">
            <h2>Pet Profile</h2>

            <div style="display:flex; align-items:center; gap:25px; flex-wrap:wrap; margin-bottom:25px;">
                @if ($pet->pet_photo_path)
                    <img src="{{ asset('storage/' . $pet->pet_photo_path) }}"
                         alt="{{ $pet->pet_name }}"
                         style="width:150px; height:150px; object-fit:cover; border-radius:15px;">
                @else
                    <div style="width:150px; height:150px; border-radius:15px; background:#f0eaff; display:flex; align-items:center; justify-content:center; color:#7652c8; font-size:45px; font-weight:bold;">
                        {{ strtoupper(substr($pet->pet_name ?? 'P', 0, 1)) }}
                    </div>
                @endif

                <div>
                    <h2 style="margin-bottom:8px;">{{ $pet->pet_name }}</h2>
                    <p style="color:#81798e;">Type: {{ $pet->pet_type }}</p>
                    <p style="color:#81798e;">
                        Birthdate:
                        {{ $pet->pet_birthdate ? $pet->pet_birthdate->format('F d, Y') : 'Not provided' }}
                    </p>
                </div>
            </div>

            <hr style="border:0; border-top:1px solid #eeeaf5; margin:25px 0;">

            <h2>Update Pet Photo</h2>

            <form method="POST"
                  action="{{ route('owner.pet-profile.update') }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <input type="hidden" name="pet_id" value="{{ $pet->id }}">

                <div class="form-group">
                    <label for="pet_photo">Choose New Photo</label>
                    <input type="file"
                           name="pet_photo"
                           id="pet_photo"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.webp">
                </div>

                <button type="submit" class="btn">Update Photo</button>
            </form>

            <hr style="border:0; border-top:1px solid #eeeaf5; margin:25px 0;">

            <h2>Update Birthdate</h2>

            <form method="POST" action="{{ route('owner.pet-profile.update') }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="pet_id" value="{{ $pet->id }}">

                <div class="form-group">
                    <label for="pet_birthdate">Pet Birthdate</label>
                    <input type="date"
                           name="pet_birthdate"
                           id="pet_birthdate"
                           class="form-control"
                           value="{{ old('pet_birthdate', $pet->pet_birthdate ? $pet->pet_birthdate->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d') }}">
                </div>

                <button type="submit" class="btn">Save Birthdate</button>
            </form>
        </div>
    @endif
@endif
@endsection
