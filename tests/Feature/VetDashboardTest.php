<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\PetOwner;
use App\Models\User;
use App\Models\VaccinationRecord;
use App\Models\Vet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VetDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createVeterinarian(string $staffType = 'veterinarian'): array
    {
        $user = User::factory()->create(['role' => 'vet']);
        $vet = Vet::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Veterinarian',
            'license_number' => $staffType === 'veterinarian' ? '0012345' : null,
            'staff_type' => $staffType,
            'specialty' => $staffType === 'veterinarian' ? 'dog' : null,
        ]);

        return [$user, $vet];
    }

    private function createPatient(array $attributes = []): array
    {
        $ownerUser = User::factory()->create(['role' => 'owner']);
        $owner = PetOwner::create([
            'user_id' => $ownerUser->id,
            'first_name' => 'Sam',
            'last_name' => 'Owner',
            'phone' => '09123456789',
        ]);
        $pet = $owner->pets()->create([
            'pet_name' => $attributes['pet_name'] ?? 'Mochi',
            'pet_type' => $attributes['pet_type'] ?? 'dog',
            'pet_birthdate' => $attributes['pet_birthdate'] ?? '2021-06-10',
        ]);

        return [$owner, $pet];
    }

    public function test_veterinarian_dashboard_shows_only_veterinary_requests_and_patient_records(): void
    {
        [$user, $vet] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPatient();
        $appointment = Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => now()->addDay(),
            'reason' => 'Annual check-up',
        ]);
        Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'groomer_id' => $vet->id,
            'service_type' => 'Grooming',
            'appointment_at' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->get(route('vet.dashboard', ['search' => 'Sam Owner']))
            ->assertOk()
            ->assertSee('Appointment requests')
            ->assertSee('Annual check-up')
            ->assertSee('Mochi')
            ->assertDontSee('Grooming');

        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_groomers_and_owners_cannot_access_veterinarian_dashboard_actions(): void
    {
        [$groomerUser] = $this->createVeterinarian('groomer');
        $this->actingAs($groomerUser)->get(route('vet.dashboard'))->assertNotFound();
        $this->get(route('groomer.dashboard'))
            ->assertOk()
            ->assertSee('Groomer Portal');

        $ownerUser = User::factory()->create(['role' => 'owner']);
        $this->actingAs($ownerUser)->put(route('vet.availability.update'), [])->assertForbidden();
    }

    public function test_groomer_login_redirects_to_the_groomer_dashboard(): void
    {
        [$groomerUser] = $this->createVeterinarian('groomer');

        $this->post(route('login'), [
            'email' => $groomerUser->email,
            'password' => 'password',
            'role' => 'vet',
        ])->assertRedirect(route('groomer.dashboard'));

        $this->assertAuthenticatedAs($groomerUser);
        $this->get(route('groomer.dashboard'))->assertOk();
    }

    public function test_vet_can_accept_and_decline_only_eligible_requests_with_reason_required(): void
    {
        [$user, $vet] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPatient();
        $request = Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'service_type' => 'Vaccination',
            'appointment_at' => now()->addDays(3)->setTime(10, 0),
        ]);

        $this->actingAs($user)
            ->patch(route('vet.appointments.update', $request), ['action' => 'accept'])
            ->assertSessionHas('status', 'Appointment request updated.');

        $this->assertDatabaseHas('appointments', [
            'id' => $request->id,
            'vet_id' => $vet->id,
            'status' => 'confirmed',
        ]);

        $this->patch(route('vet.appointments.update', $request), ['action' => 'decline'])
            ->assertSessionHasErrors('decision_reason');

        $request->update(['status' => 'pending', 'vet_id' => null]);
        $this->patch(route('vet.appointments.update', $request), [
            'action' => 'decline',
            'decision_reason' => 'Please choose another time.',
        ])->assertSessionHas('status', 'Appointment request updated.');

        $this->assertDatabaseHas('appointments', [
            'id' => $request->id,
            'status' => 'cancelled',
            'decision_reason' => 'Please choose another time.',
        ]);
    }

    public function test_vet_can_log_visit_and_vaccination_records_for_a_patient_they_treat(): void
    {
        [$user, $vet] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPatient();
        $appointment = Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => now()->subDay(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($user)
            ->post(route('vet.visits.store'), [
                'pet_id' => $pet->id,
                'appointment_id' => $appointment->id,
                'visit_date' => today()->toDateString(),
                'reason' => 'Annual examination',
                'diagnosis' => 'Healthy',
                'treatment' => 'Continue routine care',
                'prescription_medicine' => 'Example medicine',
                'prescription_dosage' => '1 tablet',
                'prescription_duration' => '5 days',
            ])
            ->assertRedirect(route('vet.dashboard', ['patient_id' => $pet->id]));

        $this->assertDatabaseHas('visit_records', [
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Healthy',
        ]);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);

        $vaccinationResponse = $this->post(route('vet.vaccinations.store'), [
            'pet_id' => $pet->id,
            'vaccine_key' => 'dog_rabies',
            'category' => 'Core',
            'date_given' => today()->toDateString(),
            'next_due_date' => today()->addYear()->toDateString(),
            'dose_number' => '1st',
            'brand' => 'Vaccine brand',
            'batch_number' => 'LOT-42',
        ]);

        $record = VaccinationRecord::latest('id')->firstOrFail();
        $vaccinationResponse->assertRedirect(route('vet.dashboard', [
            'patient_id' => $pet->id,
            'certificate' => $record->id,
        ]));
        $this->assertSame($vet->id, $record->vet_id);
        $this->get(route('vet.vaccinations.certificate', $record))->assertOk()->assertSee('Vaccination Certificate');
    }

    public function test_vet_cannot_edit_another_vets_appointment_or_patient(): void
    {
        [$user, $vet] = $this->createVeterinarian();
        [, $otherVet] = $this->createVeterinarian();
        [$owner, $pet] = $this->createPatient();
        $appointment = Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $otherVet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->patch(route('vet.appointments.update', $appointment), [
                'action' => 'accept',
            ])
            ->assertNotFound();

        $this->put(route('vet.patients.update', $pet), ['weight' => 12])
            ->assertNotFound();
    }

    public function test_availability_rejects_rescheduling_outside_working_hours(): void
    {
        [$user, $vet] = $this->createVeterinarian();
        $vet->update([
            'working_days' => ['Monday'],
            'working_hours_start' => '09:00:00',
            'working_hours_end' => '17:00:00',
        ]);
        [$owner, $pet] = $this->createPatient();
        $appointment = Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => now()->addDays(2),
        ]);
        $mondayOutsideHours = now()->next('Monday')->setTime(18, 0)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->patch(route('vet.appointments.update', $appointment), [
                'action' => 'reschedule',
                'appointment_at' => $mondayOutsideHours,
                'decision_reason' => 'Please come later.',
            ])
            ->assertStatus(422);
    }
}
