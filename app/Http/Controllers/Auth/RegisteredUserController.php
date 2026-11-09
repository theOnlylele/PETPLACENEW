<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\PetOwner;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required_with:password'],
            'role'       => ['required', 'in:vet,owner'],
            'staff_type' => ['required_if:role,vet', 'nullable', Rule::in(['veterinarian', 'groomer'])],
            'license_number' => [
                'required_if:staff_type,veterinarian',
                'nullable',
                'string',
                'regex:/\A[0-9]{7}\z/',
                Rule::unique('vets', 'license_number'),
            ],
            'specialty' => [
                'required_if:staff_type,veterinarian',
                'nullable',
                Rule::in(['dog', 'cat', 'rabbit', 'fish', 'bird', 'reptile']),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'pet_name' => ['nullable', 'string', 'max:255'],
            'pet_type' => [
                'nullable',
                Rule::in(['dog', 'cat', 'rabbit', 'fish', 'bird', 'reptile', 'other']),
            ],
            'pet_birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'pet_type_other' => ['required_if:pet_type,other', 'nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'This email is already registered. Please use a different email address.',
        ]);

        $validated['first_name'] = Str::title(mb_strtolower(trim($validated['first_name'])));
        $validated['last_name'] = Str::title(mb_strtolower(trim($validated['last_name'])));

        // Create user account
        $user = User::create([
            'name'     => $validated['first_name'].' '.$validated['last_name'],
            'email'    => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'password' => Hash::make($request->password),
            'role'     => $validated['role'],
        ]);

        // Save to separate table based on role
        if ($validated['role'] === 'vet') {
            \App\Models\Vet::create([
                'user_id'        => $user->id,
                'first_name'     => $validated['first_name'],
                'last_name'      => $validated['last_name'],
                'phone'          => $validated['phone'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'staff_type'     => $validated['staff_type'],
                'specialty'      => $validated['staff_type'] === 'veterinarian'
                    ? $validated['specialty']
                    : null,
                'approval_status' => 'pending',
            ]);
        } else {
            $petOwner = PetOwner::create([
                'user_id'    => $user->id,
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'phone'      => $validated['phone'] ?? null,
                'pet_name'   => $validated['pet_name'] ?? null,
                'pet_type'   => ($validated['pet_type'] ?? null) === 'other'
                    ? $validated['pet_type_other']
                    : ($validated['pet_type'] ?? null),
                'pet_birthdate' => $validated['pet_birthdate'] ?? null,
            ]);

            Pet::create([
                'pet_owner_id' => $petOwner->id,
                'pet_name' => $petOwner->pet_name,
                'pet_type' => $petOwner->pet_type,
                'pet_birthdate' => $validated['pet_birthdate'] ?? null,
            ]);
        }

        event(new Registered($user));

        if ($user->role === 'vet') {
            return redirect()
                ->route('login.vet')
                ->with('status', 'Your application has been submitted! Please wait for administrator approval before logging in.');
        }

        Auth::login($user);

        return redirect()->route($user->dashboardRouteName());
    }
}