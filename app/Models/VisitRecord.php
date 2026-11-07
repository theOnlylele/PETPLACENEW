<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitRecord extends Model
{
    protected $fillable = [
        'pet_id',
        'vet_id',
        'appointment_id',
        'visit_date',
        'reason',
        'diagnosis',
        'treatment',
        'prescriptions',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'follow_up_date' => 'date',
            'prescriptions' => 'array',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
