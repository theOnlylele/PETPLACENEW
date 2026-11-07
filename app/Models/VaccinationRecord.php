<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    protected $fillable = [
        'pet_id',
        'vet_id',
        'category',
        'vaccine_key',
        'date_given',
        'next_due_date',
        'veterinarian_name',
        'dose_number',
        'brand',
        'batch_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_given' => 'date',
            'next_due_date' => 'date',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function veterinarian(): BelongsTo
    {
        return $this->belongsTo(Vet::class, 'vet_id');
    }
}
