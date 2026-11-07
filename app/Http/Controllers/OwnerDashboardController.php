<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\PetOwner;
use App\Models\Vet;
use App\Models\VaccinationRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    private const CORE_VACCINES = [
        'dog' => ['dog_rabies', 'dog_cdv', 'dog_adenovirus', 'dog_parvovirus'],
        'cat' => ['cat_fvrcp', 'cat_rabies'],
    ];

    private const FVRCP_COMPONENTS = [
        'cat_rhinotracheitis',
        'cat_calicivirus',
        'cat_panleukopenia',
    ];

    private const VACCINE_OPTIONS = [
        'dog' => [
            'dog_rabies' => ['Anti-rabies', 'Helps protect against rabies, a fatal disease that can spread to people.'],
            'dog_cdv' => ['Canine Distemper Virus (CDV)', 'Protects against a serious virus affecting the respiratory, digestive, and nervous systems.'],
            'dog_adenovirus' => ['Canine Adenovirus Type 1 & 2', 'Helps protect against infectious hepatitis and respiratory disease.'],
            'dog_parvovirus' => ['Canine Parvovirus (CPV)', 'Protects against a highly contagious virus that causes severe intestinal illness.'],
            'dog_parainfluenza' => ['Canine Parainfluenza', 'Helps protect against a contagious respiratory infection and kennel cough.'],
            'dog_leptospirosis' => ['Leptospirosis', 'Helps protect against a bacterial infection that can affect the kidneys and liver and may spread to people.'],
            'dog_lyme' => ['Lyme Disease', 'Helps protect against tick-borne Lyme disease.'],
            'dog_influenza' => ['Canine Influenza (Dog Flu)', 'Helps protect against contagious canine influenza and respiratory illness.'],
            'dog_coronavirus' => ['Canine Coronavirus', 'May be considered for some dogs; ask your veterinarian about its limited indications.'],
            'dog_bordetella' => ['Bordetella bronchiseptica', 'May help protect against a contagious respiratory infection; ask your veterinarian whether it is appropriate.'],
        ],

        'cat' => [
            'cat_fvrcp' => ['FVRCP (3-in-1 Combo Vaccine)', 'A combination vaccine that covers feline viral rhinotracheitis, calicivirus, and panleukopenia.'],
            'cat_rhinotracheitis' => ['Feline Viral Rhinotracheitis', 'Helps protect against a contagious herpesvirus that causes upper respiratory disease.'],
            'cat_calicivirus' => ['Feline Calicivirus', 'Helps protect against a contagious virus that can cause respiratory illness and mouth ulcers.'],
            'cat_panleukopenia' => ['Feline Panleukopenia', 'Protects against a severe and highly contagious feline parvovirus.'],
            'cat_rabies' => ['Anti-rabies', 'Helps protect against rabies, a fatal disease that can spread to people.'],
            'cat_felv' => ['Feline Leukemia Virus (FeLV)', 'Helps protect against a virus that weakens a cat’s immune system.'],
            'cat_fiv' => ['Feline Immunodeficiency Virus (FIV)', 'Availability and suitability vary; ask your veterinarian whether it is appropriate for your cat.'],
            'cat_bordetella' => ['Bordetella bronchiseptica', 'Helps protect against a bacterial respiratory infection.'],
            'cat_fip' => ['Feline Infectious Peritonitis (FIP)', 'This vaccine is not routinely recommended; discuss its use with your veterinarian.'],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | OWNER OVERVIEW
    |--------------------------------------------------------------------------
    */

    public function show(): View
{
    $user = auth()->user();

    $petOwner = PetOwner::where('user_id', $user->id)->firstOrFail();

    $pets = $this->getOwnerPets($petOwner);

    $appointments = Appointment::whereIn(
        'pet_id',
        $pets->pluck('id')
    )->latest()->get();

    $vaccinationRecords = VaccinationRecord::whereIn(
        'pet_id',
        $pets->pluck('id')
    )->latest()->get();

    return view('owner.dashboard', compact(
        'pets',
        'appointments',
        'vaccinationRecords'
    ));
}

    /*
    |--------------------------------------------------------------------------
    | MY PETS PAGE
    |--------------------------------------------------------------------------
    */

    public function pets(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isOwner()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $pets = $this->getOwnerPets($petOwner);

        $pet = $request->filled('pet_id')
            ? $petOwner->pets()->findOrFail($request->integer('pet_id'))
            : $pets->first();

        return view('owner.pets', compact(
            'petOwner',
            'pets',
            'pet'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PET PAGE
    |--------------------------------------------------------------------------
    */

    public function createPet(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isOwner()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        return view('owner.add-pet', compact('petOwner'));
    }

    /*
    |--------------------------------------------------------------------------
    | APPOINTMENTS PAGE
    |--------------------------------------------------------------------------
    */

    public function appointments(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isOwner()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $pets = $this->getOwnerPets($petOwner);

        $pet = $request->filled('pet_id')
            ? $petOwner->pets()->findOrFail($request->integer('pet_id'))
            : $pets->first();

        $vaccinationData = $this->getVaccinationData($pet);

        $vets = Vet::where('staff_type', 'veterinarian')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $groomers = Vet::where('staff_type', 'groomer')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $appointments = Appointment::with(['vet', 'groomer', 'pet'])
            ->where('pet_owner_id', $petOwner->id)
            ->orderBy('appointment_at')
            ->get();

        return view('owner.appointments', array_merge(
            compact(
                'petOwner',
                'pets',
                'pet',
                'vets',
                'groomers',
                'appointments'
            ),
            $vaccinationData
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | VACCINATION RECORDS PAGE
    |--------------------------------------------------------------------------
    */

    public function vaccinations(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isOwner()) {
            return redirect()->route($request->user()->dashboardRouteName());
        }

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $pets = $this->getOwnerPets($petOwner);

        $pet = $request->filled('pet_id')
            ? $petOwner->pets()->findOrFail($request->integer('pet_id'))
            : $pets->first();

        $vaccinationData = $this->getVaccinationData($pet);

        return view('owner.vaccinations', array_merge(
            compact(
                'petOwner',
                'pets',
                'pet'
            ),
            $vaccinationData
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | GET OWNER PETS
    |--------------------------------------------------------------------------
    */

    private function getOwnerPets(PetOwner $petOwner)
    {
        $pets = $petOwner->pets()
            ->orderBy('id')
            ->get();

        // Support older accounts that stored their first pet
        // directly in the pet_owners table.

        if ($pets->isEmpty()) {
            $pets->push($petOwner->pets()->create([
                'pet_name' => $petOwner->pet_name,
                'pet_type' => $petOwner->pet_type,
                'pet_birthdate' => $petOwner->pet_birthdate,
                'pet_photo_path' => $petOwner->pet_photo_path,
            ]));
        }

        return $pets;
    }

    /*
    |--------------------------------------------------------------------------
    | VACCINATION DATA
    |--------------------------------------------------------------------------
    */

    private function getVaccinationData(Pet $pet): array
    {
        $petType = strtolower($pet->pet_type ?? '');

        $vaccinationRecords = $pet->vaccinationRecords()
            ->orderByDesc('date_given')
            ->orderByDesc('id')
            ->get();

        $latestVaccinationByKey = $vaccinationRecords
            ->groupBy('vaccine_key')
            ->map(fn ($records) => $records->first());

        $coreVaccineKeys = self::CORE_VACCINES[$petType] ?? [];

        $coreVaccinationRows = collect($coreVaccineKeys)->map(
            function (string $vaccineKey) use ($latestVaccinationByKey) {

                $record = $latestVaccinationByKey->get($vaccineKey);

                if (
                    $vaccineKey === 'cat_fvrcp'
                    && ! $record
                    && collect(self::FVRCP_COMPONENTS)->every(
                        fn (string $component) => $latestVaccinationByKey->has($component)
                    )
                ) {
                    $componentStatuses = collect(self::FVRCP_COMPONENTS)
                        ->map(fn (string $component) => $this->vaccinationStatus(
                            $latestVaccinationByKey->get($component)
                        ));

                    $status = $componentStatuses->contains('overdue')
                        ? 'overdue'
                        : (
                            $componentStatuses->contains('due_soon')
                                ? 'due_soon'
                                : 'up_to_date'
                        );
                } else {
                    $status = $record
                        ? $this->vaccinationStatus($record)
                        : 'not_yet_given';
                }

                return [
                    'vaccine_key' => $vaccineKey,
                    'status' => $status,
                ];
            }
        );

        $vaccinationRows = $vaccinationRecords->map(
            fn (VaccinationRecord $record) => [
                'record' => $record,
                'vaccine_key' => $record->vaccine_key,
                'status' => $this->vaccinationStatus($record),
            ]
        );

        foreach ($coreVaccinationRows->where('status', 'not_yet_given') as $coreRow) {
            $vaccinationRows->push([
                'record' => null,
                'vaccine_key' => $coreRow['vaccine_key'],
                'status' => 'not_yet_given',
            ]);
        }

        $upcomingVaccination = $vaccinationRecords
            ->filter(fn (VaccinationRecord $record) =>
                $record->next_due_date?->greaterThanOrEqualTo(today())
            )
            ->sortBy(fn (VaccinationRecord $record) => $record->next_due_date)
            ->first();

        $overdueVaccination = $vaccinationRecords
            ->filter(fn (VaccinationRecord $record) =>
                $record->next_due_date?->isBefore(today())
            )
            ->sortBy(fn (VaccinationRecord $record) => $record->next_due_date)
            ->first();

        $firstUnrecordedCore = $coreVaccinationRows->firstWhere(
            'status',
            'not_yet_given'
        );

        $vaccinationRequestKey = $upcomingVaccination?->vaccine_key
            ?? $overdueVaccination?->vaccine_key
            ?? ($firstUnrecordedCore['vaccine_key'] ?? null)
            ?? $coreVaccineKeys[0]
            ?? null;

        $coreCategoryKeys = array_unique(array_merge(
            $coreVaccineKeys,
            $petType === 'cat' ? self::FVRCP_COMPONENTS : []
        ));

        $groomingVaccines = [
            'rabies' => $latestVaccinationByKey->get(
                $petType === 'cat' ? 'cat_rabies' : 'dog_rabies'
            ),
        ];

        if ($petType === 'dog') {
            $groomingVaccines['bordetella'] = $latestVaccinationByKey->get(
                'dog_bordetella'
            );
        }

        return [
            'vaccineOptions' => self::VACCINE_OPTIONS[$petType] ?? [],

            'vaccineLabels' => collect(self::VACCINE_OPTIONS)
                ->flatMap(fn (array $options) => collect($options)->mapWithKeys(
                    fn (array $option, string $key) => [$key => $option[0]]
                ))
                ->all(),

            'vaccineCategories' => collect(array_keys(
                self::VACCINE_OPTIONS[$petType] ?? []
            ))->mapWithKeys(fn (string $key) => [
                $key => in_array($key, $coreCategoryKeys, true)
                    ? 'Core'
                    : 'Non-core (Lifestyle)',
            ])->all(),

            'vaccinationRows' => $vaccinationRows,

            'vaccinationSummary' => [
                'coreTotal' => $coreVaccinationRows->count(),
                'coreUpToDate' => $coreVaccinationRows
                    ->where('status', 'up_to_date')
                    ->count(),
                'nextDueRecord' => $upcomingVaccination,
            ],

            'vaccinationRequestKey' => $vaccinationRequestKey,

            'groomingVaccines' => collect($groomingVaccines)->map(
                fn (?VaccinationRecord $record) => [
                    'status' => $record
                        ? $this->vaccinationStatus($record)
                        : 'not_yet_given',
                    'record' => $record,
                ]
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VACCINATION STATUS
    |--------------------------------------------------------------------------
    */

    private function vaccinationStatus(VaccinationRecord $record): string
    {
        if (! $record->next_due_date) {
            return 'up_to_date';
        }

        if ($record->next_due_date->isBefore(today())) {
            return 'overdue';
        }

        if ($record->next_due_date->lessThanOrEqualTo(today()->addDays(30))) {
            return 'due_soon';
        }

        return 'up_to_date';
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PET
    |--------------------------------------------------------------------------
    */

    public function storePet(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $validated = $request->validate([
            'pet_name' => ['required', 'string', 'max:255'],
            'pet_type' => [
                'required',
                Rule::in(['dog', 'cat', 'rabbit', 'fish', 'bird', 'reptile', 'other'])
            ],
            'pet_type_other' => [
                'required_if:pet_type,other',
                'nullable',
                'string',
                'max:255'
            ],
            'pet_birthdate' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'pet_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],
        ]);

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $pet = $petOwner->pets()->create([
            'pet_name' => $validated['pet_name'],
            'pet_type' => $validated['pet_type'] === 'other'
                ? $validated['pet_type_other']
                : $validated['pet_type'],
            'pet_birthdate' => $validated['pet_birthdate'] ?? null,
        ]);

        if ($request->hasFile('pet_photo')) {
            $pet->pet_photo_path = $request->file('pet_photo')
                ->store('pet-photos', 'public');

            $pet->save();
        }

        return redirect()
            ->route('owner.pets', ['pet_id' => $pet->id])
            ->with('status', $pet->pet_name . ' was added to your pets.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PET PROFILE
    |--------------------------------------------------------------------------
    */

    public function updatePet(Request $request, ?Pet $pet = null): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $pet ??= $petOwner->pets()->findOrFail(
            $request->integer('pet_id')
        );

        abort_unless($pet->pet_owner_id === $petOwner->id, 404);

        $validated = $request->validate([
            'pet_birthdate' => [
                'nullable',
                'date',
                'before_or_equal:today'
            ],
            'pet_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],
        ]);

        if (array_key_exists('pet_birthdate', $validated)) {
            $pet->pet_birthdate = $validated['pet_birthdate'];
        }

        if ($request->hasFile('pet_photo')) {
            $newPhoto = $request->file('pet_photo')
                ->store('pet-photos', 'public');

            if ($pet->pet_photo_path) {
                Storage::disk('public')->delete($pet->pet_photo_path);
            }

            $pet->pet_photo_path = $newPhoto;
        }

        $pet->save();

        return redirect()
            ->route('owner.pets', ['pet_id' => $pet->id])
            ->with('status', 'Pet profile updated.');
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST APPOINTMENT
    |--------------------------------------------------------------------------
    */

    public function requestAppointment(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $petOwner = PetOwner::where(
            'user_id',
            $request->user()->id
        )->firstOrFail();

        $validatedPet = $request->validate([
            'pet_id' => [
                'required',
                Rule::exists('pets', 'id')
                    ->where('pet_owner_id', $petOwner->id)
            ],
        ]);

        $pet = $petOwner->pets()->findOrFail($validatedPet['pet_id']);

        $petType = strtolower($pet->pet_type ?? '');

        $isDogOrCat = in_array($petType, ['dog', 'cat'], true);

        $requiresVaccineSelection =
            $request->input('service_type') === 'Vaccination'
            && $isDogOrCat;

        $validated = $request->validate([
            'service_type' => [
                'required',
                Rule::in([
                    'Check-up / Consultation',
                    'Vaccination',
                    'Grooming'
                ])
            ],

            'vet_id' => [
                'nullable',
                Rule::exists('vets', 'id')
                    ->where('staff_type', 'veterinarian'),
            ],

            'groomer_id' => [
                'nullable',
                Rule::exists('vets', 'id')
                    ->where('staff_type', 'groomer'),
            ],

            'appointment_at' => [
                'required',
                'date',
                'after:now'
            ],

            'reason' => [
                'nullable',
                'string',
                'max:1000'
            ],

            'vaccines' => $requiresVaccineSelection
                ? ['required', 'array', 'min:1']
                : ['prohibited'],

            'grooming_addons' =>
                $request->input('service_type') === 'Grooming'
                    ? ['nullable', 'array']
                    : ['prohibited'],

            'grooming_addons.*' => [
                'required',
                'string',
                Rule::in([
                    'nail_trimming',
                    'ear_cleaning',
                    'teeth_brushing',
                    'anal_gland_expression',
                    'flea_tick_treatment',
                    'de_shedding',
                    'medicated_shampoo',
                    'perfume_bow_bandana',
                ])
            ],

            'haircut_style' => $this->groomingRule(
                $request,
                ['Short', 'Medium', 'Long', 'Same as last time', 'Let the groomer decide']
            ),

            'shampoo_type' => $this->groomingRule(
                $request,
                ['Regular', 'Hypoallergenic', 'Medicated']
            ),

            'fur_matted' =>
                $request->input('service_type') === 'Grooming'
                    ? ['nullable', Rule::in(['0', '1', 0, 1])]
                    : ['prohibited'],

            'pet_size' => $this->groomingRule(
                $request,
                ['Small', 'Medium', 'Large', 'Giant']
            ),

            'coat_type' => $this->groomingRule(
                $request,
                ['Short', 'Long', 'Curly', 'Double coat']
            ),

            'temperament' => $this->groomingRule(
                $request,
                ['Fine around strangers', 'Nervous', 'Aggressive']
            ),

            'allergies_or_skin_problems' =>
                $request->input('service_type') === 'Grooming'
                    ? ['nullable', 'string', 'max:1000']
                    : ['prohibited'],
        ]);

        $vaccineChoices = array_keys(
            self::VACCINE_OPTIONS[$petType] ?? []
        );

        $selectedVaccines = $validated['vaccines'] ?? [];

        if ($validated['service_type'] === 'Vaccination' && $isDogOrCat) {
            $request->validate([
                'vaccines.*' => [
                    'required',
                    'string',
                    Rule::in($vaccineChoices)
                ],
            ]);

            if (
                $petType === 'cat'
                && in_array('cat_fvrcp', $selectedVaccines, true)
            ) {
                $selectedVaccines = array_values(array_unique(array_merge(
                    $selectedVaccines,
                    [
                        'cat_rhinotracheitis',
                        'cat_calicivirus',
                        'cat_panleukopenia',
                    ]
                )));
            } elseif (
                $petType === 'cat'
                && count(array_intersect(
                    [
                        'cat_rhinotracheitis',
                        'cat_calicivirus',
                        'cat_panleukopenia',
                    ],
                    $selectedVaccines
                )) === 3
            ) {
                array_unshift($selectedVaccines, 'cat_fvrcp');
            }
        } else {
            $selectedVaccines = [];
        }

        if (
            $validated['service_type'] !== 'Grooming'
            && ! empty($validated['vet_id'])
        ) {
            $selectedVet = Vet::findOrFail($validated['vet_id']);

            if (! $this->isWithinVetAvailability(
                $selectedVet,
                Carbon::parse($validated['appointment_at'])
            )) {
                throw ValidationException::withMessages([
                    'appointment_at' =>
                        'The selected veterinarian is not available at that date and time.',
                ]);
            }
        }

        Appointment::create([
            'pet_owner_id' => $petOwner->id,
            'pet_id' => $pet->id,

            'service_type' => $validated['service_type'],

            'vet_id' => $validated['service_type'] === 'Grooming'
                ? null
                : ($validated['vet_id'] ?? null),

            'groomer_id' => $validated['service_type'] === 'Grooming'
                ? ($validated['groomer_id'] ?? null)
                : null,

            'vaccines' => $selectedVaccines,

            'grooming_addons' =>
                $validated['service_type'] === 'Grooming'
                    ? ($validated['grooming_addons'] ?? [])
                    : null,

            'haircut_style' => $validated['haircut_style'] ?? null,
            'shampoo_type' => $validated['shampoo_type'] ?? null,

            'fur_matted' => isset($validated['fur_matted'])
                ? (bool) $validated['fur_matted']
                : null,

            'pet_size' => $validated['pet_size'] ?? null,
            'coat_type' => $validated['coat_type'] ?? null,
            'temperament' => $validated['temperament'] ?? null,

            'allergies_or_skin_problems' =>
                $validated['allergies_or_skin_problems'] ?? null,

            'appointment_at' => $validated['appointment_at'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with(
            'status',
            'Your appointment request has been sent.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GROOMING VALIDATION
    |--------------------------------------------------------------------------
    */

    private function groomingRule(Request $request, array $choices): array
    {
        return $request->input('service_type') === 'Grooming'
            ? ['nullable', Rule::in($choices)]
            : ['prohibited'];
    }

    /*
    |--------------------------------------------------------------------------
    | VETERINARIAN AVAILABILITY
    |--------------------------------------------------------------------------
    */

    private function isWithinVetAvailability(
        Vet $vet,
        Carbon $appointmentAt
    ): bool {
        if (in_array(
            $appointmentAt->toDateString(),
            $vet->days_off ?? [],
            true
        )) {
            return false;
        }

        if (
            empty($vet->working_days)
            || ! $vet->working_hours_start
            || ! $vet->working_hours_end
        ) {
            return true;
        }

        return in_array(
            $appointmentAt->format('l'),
            $vet->working_days,
            true
        )
            && $appointmentAt->format('H:i:s') >= $vet->working_hours_start
            && $appointmentAt->format('H:i:s') < $vet->working_hours_end;
    }
}
