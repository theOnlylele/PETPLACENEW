<?php

namespace Tests\Feature;

use App\Models\PetOwner;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Vet;
use App\Models\VaccinationRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerWithPet(array $attributes): array
    {
        $petOwner = PetOwner::create([
            'user_id' => $attributes['user_id'],
            'first_name' => $attributes['first_name'] ?? 'Sam',
            'last_name' => $attributes['last_name'] ?? 'Owner',
        ]);
        $pet = $petOwner->pets()->create([
            'pet_name' => $attributes['pet_name'] ?? null,
            'pet_type' => $attributes['pet_type'] ?? null,
            'pet_birthdate' => $attributes['pet_birthdate'] ?? null,
        ]);

        return [$petOwner, $pet];
    }

    public function test_owner_dashboard_shows_registered_pet_information(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
        ]);

        $this->actingAs($user);
        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Mochi')
            ->assertSee('Dog')
            ->assertDontSee('class="pet-banner"', false)
            ->assertSee('Add additional pet')
            ->assertSeeInOrder(['id="pet-profile-title"', 'for="pet_id"', 'View pet', 'Add additional pet'])
            ->assertSee('Add birthdate')
            ->assertSee('Request an appointment')
            ->assertSee('Anti-rabies')
            ->assertSee('Canine Distemper Virus (CDV)')
            ->assertDontSee('FVRCP (3-in-1 Combo Vaccine)')
            ->assertDontSee('Pet owner dashboard');

        $this->assertDatabaseHas('pet_owners', [
            'id' => $petOwner->id,
            'pet_birthdate' => null,
        ]);
    }

    public function test_generic_dashboard_sends_owner_to_pet_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('owner.dashboard'));
    }

    public function test_pet_age_uses_months_or_days_when_under_one_year_old(): void
    {
        $this->travelTo(now()->startOfDay());
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
            'pet_birthdate' => now()->subDays(12)->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('(12 days old)');

        $pet->update(['pet_birthdate' => now()->subMonths(3)->toDateString()]);

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('(3 months old)');

        $this->travelBack();
    }

    public function test_owner_can_add_pet_birthdate_and_request_an_appointment(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
        ]);
        $vetUser = User::factory()->create(['role' => 'vet']);
        $vet = Vet::create([
            'user_id' => $vetUser->id,
            'first_name' => 'Alex',
            'last_name' => 'Vet',
            'license_number' => 'VET-123',
            'staff_type' => 'veterinarian',
            'specialty' => 'dog',
        ]);

        $this->actingAs($user)
            ->patch(route('owner.pet-profile.update', $pet), [
                'pet_birthdate' => '2022-05-10',
            ])
            ->assertSessionHas('status', 'Pet profile updated.');

        $appointmentAt = now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i');

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $pet->id,
            'service_type' => 'Check-up / Consultation',
            'vet_id' => $vet->id,
            'appointment_at' => $appointmentAt,
            'reason' => 'Annual check-up',
        ])->assertSessionHas('status', 'Your appointment request has been sent.');

        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'pet_birthdate' => '2022-05-10 00:00:00',
        ]);
        $this->assertDatabaseHas('appointments', [
            'pet_owner_id' => $petOwner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'reason' => 'Annual check-up',
            'status' => 'pending',
        ]);

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Dr. Alex Vet')
            ->assertSee('Check-up / Consultation')
            ->assertSee('Annual check-up');
    }

    public function test_owner_cannot_book_a_veterinarian_on_a_day_off(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
        ]);
        $vetUser = User::factory()->create(['role' => 'vet']);
        $vet = Vet::create([
            'user_id' => $vetUser->id,
            'first_name' => 'Alex',
            'last_name' => 'Vet',
            'license_number' => 'VET-123',
            'staff_type' => 'veterinarian',
            'specialty' => 'dog',
            'working_days' => ['Monday'],
            'working_hours_start' => '09:00:00',
            'working_hours_end' => '17:00:00',
        ]);
        $dayOff = now()->next('Monday')->setTime(10, 30);
        $vet->update(['days_off' => [$dayOff->toDateString()]]);

        $this->actingAs($user)
            ->from(route('owner.dashboard'))
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Check-up / Consultation',
                'vet_id' => $vet->id,
                'appointment_at' => $dayOff->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('owner.dashboard'))
            ->assertSessionHasErrors('appointment_at');

        $this->assertDatabaseMissing('appointments', [
            'pet_owner_id' => $petOwner->id,
            'vet_id' => $vet->id,
        ]);
    }

    public function test_grooming_requests_can_target_a_groomer_or_any_groomer(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'dog',
        ]);
        $groomerUser = User::factory()->create(['role' => 'vet']);
        $groomer = Vet::create([
            'user_id' => $groomerUser->id,
            'first_name' => 'Jamie',
            'last_name' => 'Groomer',
            'license_number' => 'GROOM-123',
            'staff_type' => 'groomer',
        ]);
        $pet->vaccinationRecords()->create([
            'vaccine_key' => 'dog_rabies',
            'date_given' => now()->subMonths(6)->toDateString(),
            'next_due_date' => now()->addDays(15)->toDateString(),
        ]);
        $appointmentAt = now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Grooming options')
            ->assertSee('Nail trimming')
            ->assertSee('Ear cleaning')
            ->assertSee('Teeth brushing')
            ->assertSee('Anal gland expression')
            ->assertSee('Flea &amp; tick treatment', false)
            ->assertSee('De-shedding treatment')
            ->assertSee('Hypoallergenic')
            ->assertSee('Matted or tangled')
            ->assertSee('Pet size or weight range')
            ->assertSee('Coat type')
            ->assertSee('Temperament around strangers')
            ->assertSee("Vaccination status from the pet's records", false)
            ->assertSee('Rabies · due '.now()->addDays(15)->format('M j, Y'))
            ->assertSee('Due soon')
            ->assertSee('Bordetella')
            ->assertSee('groomingFields.hidden = !isGrooming', false);

        $this->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Grooming',
                'groomer_id' => $groomer->id,
                'grooming_addons' => ['nail_trimming', 'medicated_shampoo'],
                'haircut_style' => 'Same as last time',
                'shampoo_type' => 'Hypoallergenic',
                'fur_matted' => '1',
                'pet_size' => 'Large',
                'coat_type' => 'Double coat',
                'temperament' => 'Nervous',
                'allergies_or_skin_problems' => 'Sensitive skin',
                'reason' => 'Please be gentle.',
                'appointment_at' => $appointmentAt,
            ])
            ->assertSessionHasNoErrors();

        $appointment = Appointment::where('service_type', 'Grooming')->firstOrFail();
        $this->assertSame($petOwner->id, $appointment->pet_owner_id);
        $this->assertNull($appointment->vet_id);
        $this->assertSame($groomer->id, $appointment->groomer_id);
        $this->assertSame(['nail_trimming', 'medicated_shampoo'], $appointment->grooming_addons);
        $this->assertSame('Same as last time', $appointment->haircut_style);
        $this->assertSame('Hypoallergenic', $appointment->shampoo_type);
        $this->assertTrue($appointment->fur_matted);
        $this->assertSame('Large', $appointment->pet_size);
        $this->assertSame('Double coat', $appointment->coat_type);
        $this->assertSame('Nervous', $appointment->temperament);
        $this->assertSame('Sensitive skin', $appointment->allergies_or_skin_problems);
        $this->assertSame('Please be gentle.', $appointment->reason);

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $pet->id,
            'service_type' => 'Grooming',
            'appointment_at' => now()->addDays(4)->setTime(10, 30)->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'pet_owner_id' => $petOwner->id,
            'service_type' => 'Grooming',
            'groomer_id' => null,
        ]);

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Grooming add-ons: Nail Trimming, Medicated Shampoo')
            ->assertSee('Haircut: Same as last time')
            ->assertSee('Sensitive skin');
    }

    public function test_veterinary_appointments_do_not_assign_groomers(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'dog',
        ]);
        $groomerUser = User::factory()->create(['role' => 'vet']);
        $groomer = Vet::create([
            'user_id' => $groomerUser->id,
            'first_name' => 'Jamie',
            'last_name' => 'Groomer',
            'license_number' => 'GROOM-123',
            'staff_type' => 'groomer',
        ]);

        $this->actingAs($user)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Vaccination',
                'vaccines' => ['dog_rabies'],
                'groomer_id' => $groomer->id,
                'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasNoErrors();

        $appointment = Appointment::where('service_type', 'Vaccination')->firstOrFail();
        $this->assertSame(['dog_rabies'], $appointment->vaccines);
        $this->assertNull($appointment->vet_id);
        $this->assertNull($appointment->groomer_id);
        $this->assertNull($appointment->grooming_addons);
        $this->assertNull($appointment->haircut_style);
    }

    public function test_cat_vaccination_displays_choices_and_fvrcp_saves_all_three_components(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'cat',
        ]);
        $appointmentAt = now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('FVRCP (3-in-1 Combo Vaccine)')
            ->assertSee('Feline Viral Rhinotracheitis')
            ->assertSee('Feline Calicivirus')
            ->assertSee('Feline Panleukopenia')
            ->assertSee('Feline Leukemia Virus (FeLV)')
            ->assertDontSee('Canine Distemper Virus (CDV)');

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $pet->id,
            'service_type' => 'Vaccination',
            'vaccines' => ['cat_fvrcp'],
            'appointment_at' => $appointmentAt,
        ])->assertSessionHasNoErrors();

        $comboAppointment = Appointment::where('pet_owner_id', $petOwner->id)->firstOrFail();
        $this->assertSame([
            'cat_fvrcp',
            'cat_rhinotracheitis',
            'cat_calicivirus',
            'cat_panleukopenia',
        ], $comboAppointment->vaccines);

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Vaccines requested: FVRCP (3-in-1 Combo Vaccine), Feline Viral Rhinotracheitis, Feline Calicivirus, Feline Panleukopenia');
    }

    public function test_cat_vaccines_can_be_selected_individually_and_other_species_vaccines_are_rejected(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'cat',
        ]);
        $appointmentAt = now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Vaccination',
                'vaccines' => ['cat_rhinotracheitis', 'cat_rabies'],
                'appointment_at' => $appointmentAt,
            ])
            ->assertSessionHasNoErrors();

        $individualAppointment = Appointment::where('pet_owner_id', $petOwner->id)->firstOrFail();
        $this->assertSame(['cat_rhinotracheitis', 'cat_rabies'], $individualAppointment->vaccines);

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $pet->id,
            'service_type' => 'Vaccination',
            'vaccines' => ['dog_rabies'],
            'appointment_at' => now()->addDays(4)->setTime(10, 30)->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('vaccines.0');
    }

    public function test_selecting_all_three_cat_vaccine_components_includes_fvrcp_combo(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'cat',
        ]);

        $this->actingAs($user)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Vaccination',
                'vaccines' => [
                    'cat_rhinotracheitis',
                    'cat_calicivirus',
                    'cat_panleukopenia',
                ],
                'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasNoErrors();

        $appointment = Appointment::where('pet_owner_id', $petOwner->id)->firstOrFail();
        $this->assertSame([
            'cat_fvrcp',
            'cat_rhinotracheitis',
            'cat_calicivirus',
            'cat_panleukopenia',
        ], $appointment->vaccines);
    }

    public function test_dog_or_cat_vaccination_requires_a_vaccine_selection(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'dog',
        ]);

        $this->actingAs($user)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Vaccination',
                'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('vaccines');
    }

    public function test_vaccine_choices_are_only_shown_for_dogs_and_cats(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'reptile',
        ]);

        $this->actingAs($user)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertDontSee('id="vaccines-field"', false)
            ->assertDontSee('Canine Distemper Virus (CDV)')
            ->assertDontSee('Feline Calicivirus');

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $pet->id,
            'service_type' => 'Vaccination',
            'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $appointment = Appointment::where(
            'pet_owner_id',
            PetOwner::where('user_id', $user->id)->value('id')
        )->firstOrFail();
        $this->assertSame('Vaccination', $appointment->service_type);
        $this->assertSame([], $appointment->vaccines);
    }

    public function test_owner_can_add_and_select_another_pet_for_appointments(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [$petOwner, $firstPet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'pet_name' => 'Rex',
            'pet_type' => 'dog',
        ]);

        $response = $this->actingAs($user)->post(route('owner.pets.store'), [
            'pet_name' => 'Mimi',
            'pet_type' => 'cat',
            'pet_birthdate' => '2024-01-15',
        ]);
        $secondPet = $petOwner->pets()->where('pet_name', 'Mimi')->firstOrFail();

        $response->assertRedirect(route('owner.dashboard', ['pet_id' => $secondPet->id]));
        $this->get(route('owner.dashboard', ['pet_id' => $secondPet->id]))
            ->assertOk()
            ->assertSee('Mimi')
            ->assertSee('FVRCP (3-in-1 Combo Vaccine)')
            ->assertDontSee('Canine Distemper Virus (CDV)');

        $this->post(route('owner.appointments.store'), [
            'pet_id' => $secondPet->id,
            'service_type' => 'Vaccination',
            'vaccines' => ['cat_rabies'],
            'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();

        $appointment = Appointment::where('pet_owner_id', $petOwner->id)->firstOrFail();
        $this->assertSame($secondPet->id, $appointment->pet_id);
        $this->assertNotSame($firstPet->id, $appointment->pet_id);
        $this->assertSame(['cat_rabies'], $appointment->vaccines);
    }

    public function test_owner_can_view_vaccination_record_and_due_status(): void
    {
        $this->travelTo(now()->startOfDay());
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_name' => 'Mochi',
            'pet_type' => 'dog',
        ]);
        $givenDate = now()->subMonths(5)->toDateString();
        $dueDate = now()->addDays(15)->toDateString();

        $pet->vaccinationRecords()->create([
                'vaccine_key' => 'dog_cdv',
                'date_given' => $givenDate,
                'next_due_date' => $dueDate,
                'veterinarian_name' => 'Dr. Lee',
                'dose_number' => '2nd',
                'brand' => 'Example brand',
                'batch_number' => 'LOT-123',
                'notes' => 'Mild swelling afterward.',
        ]);

        $this->actingAs($user)
            ->get(route('owner.dashboard', ['pet_id' => $pet->id]))
            ->assertOk()
            ->assertSee("0 of 4 core vaccines up to date")
            ->assertSee('Canine Distemper Virus (CDV)')
            ->assertSee('Core')
            ->assertSee('Due soon')
            ->assertSee('Mild swelling afterward.')
            ->assertSee('Dr. Lee')
            ->assertSee('LOT-123')
            ->assertSee('Request vaccination')
            ->assertSee('Vaccination history and due dates entered by your veterinary team.')
            ->assertDontSee('Add vaccination record')
            ->assertDontSee('Save record')
            ->assertDontSee('name="vaccine_key"', false);

        $this->travelBack();
    }

    public function test_vaccination_request_link_prefills_service_and_vaccine(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'cat',
        ]);

        $this->actingAs($user)
            ->get(route('owner.dashboard', [
                'pet_id' => $pet->id,
                'service_type' => 'Vaccination',
                'vaccine' => 'cat_fvrcp',
            ]))
            ->assertOk()
            ->assertSee('value="Vaccination" selected', false)
            ->assertSee('value="cat_fvrcp" checked', false);
    }

    public function test_vaccination_dashboard_shows_all_status_badges_and_next_due_summary(): void
    {
        $this->travelTo(now()->startOfDay());
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'dog',
        ]);
        $givenDate = now()->subMonths(6)->toDateString();
        $soonDate = now()->addDays(15)->toDateString();

        $pet->vaccinationRecords()->createMany([
            [
                'vaccine_key' => 'dog_rabies',
                'date_given' => $givenDate,
                'next_due_date' => now()->addDays(45)->toDateString(),
            ],
            [
                'vaccine_key' => 'dog_cdv',
                'date_given' => $givenDate,
                'next_due_date' => $soonDate,
            ],
            [
                'vaccine_key' => 'dog_adenovirus',
                'date_given' => $givenDate,
                'next_due_date' => now()->subDay()->toDateString(),
            ],
        ]);

        $this->actingAs($user)
            ->get(route('owner.dashboard', ['pet_id' => $pet->id]))
            ->assertOk()
            ->assertSee('1 of 4 core vaccines up to date')
            ->assertSee('Next due: Canine Distemper Virus (CDV) · '.now()->addDays(15)->format('M j, Y'))
            ->assertSee('Up to date')
            ->assertSee('Due soon')
            ->assertSee('Overdue')
            ->assertSee('Not yet given');

        $this->travelBack();
    }

    public function test_vaccination_records_have_no_owner_side_write_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        [, $pet] = $this->createOwnerWithPet([
            'user_id' => $user->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'pet_type' => 'dog',
        ]);

        $this->actingAs($user)
            ->post('/owner/vaccination-records', [
                'pet_id' => $pet->id,
                'vaccine_key' => 'dog_rabies',
                'date_given' => now()->subDay()->toDateString(),
            ])
            ->assertNotFound();

        $this->assertSame(0, VaccinationRecord::count());
    }

    public function test_owner_cannot_book_another_owners_pet(): void
    {
        $ownerUser = User::factory()->create(['role' => 'owner']);
        $this->createOwnerWithPet([
            'user_id' => $ownerUser->id,
            'pet_name' => 'Owner pet',
            'pet_type' => 'cat',
        ]);
        $otherUser = User::factory()->create(['role' => 'owner']);
        [, $otherPet] = $this->createOwnerWithPet([
            'user_id' => $otherUser->id,
            'pet_name' => 'Private pet',
            'pet_type' => 'dog',
        ]);

        $this->actingAs($ownerUser)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $otherPet->id,
                'service_type' => 'Check-up / Consultation',
                'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('pet_id');

        $this->assertDatabaseCount('appointments', 0);
    }
}
