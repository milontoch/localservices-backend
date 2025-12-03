<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'password',
        'latitude',
        'longitude',
        'phone_verified',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'phone_verified' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function contactRecords()
    {
        return $this->hasMany(ContactRecord::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => 'user'
        ];
    }
}
