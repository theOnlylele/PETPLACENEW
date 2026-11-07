<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'pet_owner_id',
        'pet_id',
        'vet_id',
        'groomer_id',
        'service_type',
        'vaccines',
        'grooming_addons',
        'haircut_style',
        'shampoo_type',
        'fur_matted',
        'pet_size',
        'coat_type',
        'temperament',
        'allergies_or_skin_problems',
        'appointment_at',
        'reason',
        'status',
        'decision_reason',
    ];

    protected function casts(): array
    {
        return [
            'appointment_at' => 'datetime',
            'vaccines' => 'array',
            'grooming_addons' => 'array',
            'fur_matted' => 'boolean',
        ];
    }

    public function petOwner(): BelongsTo
    {
        return $this->belongsTo(PetOwner::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function vet(): BelongsTo
    {
        return $this->belongsTo(Vet::class);
    }

    public function groomer(): BelongsTo
    {
        return $this->belongsTo(Vet::class, 'groomer_id');
    }
}
