<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vet extends Model
{
   protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'license_number',
    'clinic_name',
    'staff_type',
    'specialty',
    'approval_status',
    'working_days',
    'working_hours_start',
    'working_hours_end',
    'days_off',
    'phone',
    'profile_photo',
];
    protected function casts(): array
    {
        return [
            'working_days' => 'array',
            'days_off' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function groomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'groomer_id');
    }

    public function visitRecords(): HasMany
    {
        return $this->hasMany(VisitRecord::class);
    }

    public function vaccinationRecords(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }
}
