<?php

namespace Tests\Feature;

use App\Models\PetOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAndPetRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_forms_offer_staff_roles_and_supported_pet_types(): void
    {
        $this->get(route('register.vet'))
            ->assertOk()
            ->assertSee('Staff Registration')
            ->assertSee('name="staff_type"', false)
            ->assertSee('value="groomer"', false)
            ->assertSee('name="specialty"', false)
            ->assertSee('id="specialty" required', false)
            ->assertSee('id="licenseField"', false)
            ->assertSee('licenseInput.required = isVeterinarian', false)
            ->assertSee('autocomplete="off" required', false)
            ->assertSee('pattern="[0-9]{7}"', false)
            ->assertSee('maxlength="7"', false)
            ->assertSee('inputmode="numeric"', false)
            ->assertSee('placeholder="7 digits, e.g. 0012345"', false)
            ->assertSee('name="date_of_birth"', false)
            ->assertSee('name="date_of_birth" max=', false)
            ->assertDontSee('clinic_name');

        $this->get(route('register.owner'))
            ->assertOk()
            ->assertSee('value="rabbit"', false)
            ->assertSee('value="fish"', false)
            ->assertSee('value="bird"', false)
            ->assertSee('value="reptile"', false)
            ->assertSee('id="otherPetTypeField"', false)
            ->assertSee('name="pet_type_other"', false)
            ->assertSee('name="date_of_birth"', false)
            ->assertSee('name="pet_birthdate"', false)
            ->assertSee('id="pet_birthdate"', false)
            ->assertSee('name="pet_birthdate"', false)
            ->assertSee('max="'.today()->toDateString().'"', false);
    }

    public function test_groomer_can_register_without_a_veterinary_specialty(): void
    {
        $this->post(route('register'), [
            'first_name' => 'JAMIE',
            'last_name' => 'GROOMER',
            'email' => 'jamie@example.com',
            'date_of_birth' => '1990-04-12',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'vet',
            'staff_type' => 'groomer',
        ])->assertRedirect(route('groomer.dashboard'));

        $this->assertDatabaseHas('vets', [
            'first_name' => 'Jamie',
            'last_name' => 'Groomer',
            'license_number' => null,
            'staff_type' => 'groomer',
            'specialty' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'jamie@example.com',
            'name' => 'Jamie Groomer',
        ]);
        $registeredUser = \App\Models\User::where('email', 'jamie@example.com')->firstOrFail();
        $this->assertSame('1990-04-12', $registeredUser->date_of_birth->toDateString());
    }

    public function test_veterinarian_registration_requires_and_saves_specialty(): void
    {
        $payload = [
            'first_name' => 'Alex',
            'last_name' => 'Vet',
            'email' => 'alex@example.com',
            'date_of_birth' => '1985-09-10',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'vet',
            'staff_type' => 'veterinarian',
            'license_number' => '0012345',
        ];

        $this->from(route('register.vet'))
            ->post(route('register'), $payload)
            ->assertRedirect(route('register.vet'))
            ->assertSessionHasErrors('specialty');

        $this->assertDatabaseMissing('users', ['email' => 'alex@example.com']);

        $payload['specialty'] = 'fish';
        $licenseNumber = $payload['license_number'];
        unset($payload['license_number']);

        $this->from(route('register.vet'))
            ->post(route('register'), $payload)
            ->assertRedirect(route('register.vet'))
            ->assertSessionHasErrors('license_number');

        foreach (['VET-123', '123456', '12345678', '12-3456'] as $invalidLicense) {
            $payload['license_number'] = $invalidLicense;

            $this->from(route('register.vet'))
                ->post(route('register'), $payload)
                ->assertRedirect(route('register.vet'))
                ->assertSessionHasErrors('license_number');
        }

        $payload['license_number'] = $licenseNumber;
        $this->post(route('register'), $payload)
            ->assertRedirect(route('vet.dashboard'));

        $this->assertDatabaseHas('vets', [
            'license_number' => '0012345',
            'staff_type' => 'veterinarian',
            'specialty' => 'fish',
        ]);
    }

    public function test_veterinarian_prc_license_number_must_be_unique(): void
    {
        $existingUser = \App\Models\User::factory()->create(['role' => 'vet']);
        \App\Models\Vet::create([
            'user_id' => $existingUser->id,
            'first_name' => 'Existing',
            'last_name' => 'Vet',
            'license_number' => '0012345',
            'staff_type' => 'veterinarian',
            'specialty' => 'dog',
        ]);

        $this->from(route('register.vet'))
            ->post(route('register'), [
                'first_name' => 'New',
                'last_name' => 'Vet',
                'email' => 'newvet@example.com',
                'date_of_birth' => '1985-09-10',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'vet',
                'staff_type' => 'veterinarian',
                'specialty' => 'cat',
                'license_number' => '0012345',
            ])
            ->assertRedirect(route('register.vet'))
            ->assertSessionHasErrors('license_number');

        $this->assertDatabaseMissing('users', ['email' => 'newvet@example.com']);
    }

    public function test_staff_registration_requires_all_fields_for_selected_role(): void
    {
        $completeVeterinarian = [
            'first_name' => 'Alex',
            'last_name' => 'Vet',
            'email' => 'completevet@example.com',
            'date_of_birth' => '1985-09-10',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'vet',
            'staff_type' => 'veterinarian',
            'specialty' => 'dog',
            'license_number' => '0012345',
        ];

        foreach (['first_name', 'last_name', 'email', 'date_of_birth', 'password', 'password_confirmation', 'staff_type', 'specialty', 'license_number'] as $requiredField) {
            $payload = $completeVeterinarian;
            unset($payload[$requiredField]);

            $this->from(route('register.vet'))
                ->post(route('register'), $payload)
                ->assertRedirect(route('register.vet'))
                ->assertSessionHasErrors($requiredField);
        }

        $this->assertDatabaseMissing('users', ['email' => 'completevet@example.com']);
    }

    public function test_staff_registration_rejects_an_email_already_in_use(): void
    {
        \App\Models\User::factory()->create(['email' => 'used@example.com']);

        $this->from(route('register.vet'))
            ->post(route('register'), [
                'first_name' => 'Alex',
                'last_name' => 'Vet',
                'email' => 'used@example.com',
                'date_of_birth' => '1985-09-10',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'vet',
                'staff_type' => 'veterinarian',
                'specialty' => 'dog',
                'license_number' => '0012345',
            ])
            ->assertRedirect(route('register.vet'))
            ->assertSessionHasErrors([
                'email' => 'This email is already registered. Please use a different email address.',
            ]);

        $this->assertSame(1, \App\Models\User::where('email', 'used@example.com')->count());
    }

    public function test_registration_rejects_a_future_person_birthdate(): void
    {
        $this->from(route('register.vet'))
            ->post(route('register'), [
                'first_name' => 'Alex',
                'last_name' => 'Vet',
                'email' => 'futurevet@example.com',
                'date_of_birth' => now()->addDay()->toDateString(),
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'vet',
                'staff_type' => 'groomer',
            ])
            ->assertRedirect(route('register.vet'))
            ->assertSessionHasErrors('date_of_birth');

        $this->assertDatabaseMissing('users', ['email' => 'futurevet@example.com']);
    }

    public function test_owner_can_register_with_a_custom_other_pet_type(): void
    {
        $this->post(route('register'), [
            'first_name' => 'SAM',
            'last_name' => 'OWNER',
            'email' => 'sam@example.com',
            'date_of_birth' => '1992-11-03',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'other',
            'pet_type_other' => 'Hamster',
            'pet_birthdate' => '2024-02-14',
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertDatabaseHas('pet_owners', [
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'Hamster',
        ]);
        $registeredUser = \App\Models\User::where('email', 'sam@example.com')->firstOrFail();
        $this->assertSame('1992-11-03', $registeredUser->date_of_birth->toDateString());
        $this->assertSame('Sam Owner', $registeredUser->name);
        $this->assertDatabaseHas('pets', [
            'pet_name' => 'Mochi',
            'pet_type' => 'Hamster',
            'pet_birthdate' => '2024-02-14 00:00:00',
        ]);
        $this->assertDatabaseHas('pet_owners', [
            'pet_name' => 'Mochi',
            'pet_birthdate' => '2024-02-14 00:00:00',
        ]);
    }

    public function test_owner_registration_rejects_a_future_pet_birthdate(): void
    {
        $this->from(route('register.owner'))
            ->post(route('register'), [
                'first_name' => 'Sam',
                'last_name' => 'Owner',
                'email' => 'sam@example.com',
                'date_of_birth' => '1992-11-03',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'owner',
                'pet_name' => 'Mochi',
                'pet_type' => 'cat',
                'pet_birthdate' => now()->addDay()->toDateString(),
            ])
            ->assertRedirect(route('register.owner'))
            ->assertSessionHasErrors('pet_birthdate');

        $this->assertDatabaseMissing('users', ['email' => 'sam@example.com']);
    }

    public function test_owner_must_describe_other_pet_type(): void
    {
        $this->from(route('register.owner'))
            ->post(route('register'), [
                'first_name' => 'Sam',
                'last_name' => 'Owner',
                'email' => 'sam@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'owner',
                'pet_type' => 'other',
            ])
            ->assertRedirect(route('register.owner'))
            ->assertSessionHasErrors('pet_type_other');

        $this->assertDatabaseMissing('users', ['email' => 'sam@example.com']);
    }
}
