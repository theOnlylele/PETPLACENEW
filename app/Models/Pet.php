<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    protected $fillable = [
        'pet_owner_id',
        'pet_name',
        'pet_type',
        'pet_birthdate',
        'pet_photo_path',
        'breed',
        'weight',
        'allergies',
    ];

    protected function casts(): array
    {
        return [
            'pet_birthdate' => 'date',
        ];
    }

    public function petOwner(): BelongsTo
    {
        return $this->belongsTo(PetOwner::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function vaccinationRecords(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }

    public function visitRecords(): HasMany
    {
        return $this->hasMany(VisitRecord::class);
    }
}
