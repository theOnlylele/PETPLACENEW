<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'date_of_birth',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    public function isVet(): bool
    {
        return $this->role === 'vet';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function vetProfile(): HasOne
    {
        return $this->hasOne(Vet::class);
    }

    public function dashboardRouteName(): string
    {
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($this->isOwner()) {
            return 'owner.dashboard';
        }

        return $this->vetProfile()->value('staff_type') === 'groomer'
            ? 'groomer.dashboard'
            : 'vet.dashboard';
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}