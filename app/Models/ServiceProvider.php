<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ServiceProvider extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'password',
        'category_id',
        'latitude',
        'longitude',
        'experience_years',
        'rating_avg',
        'is_verified',
        'phone_verified',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating_avg' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function portfolios()
    {
        return $this->hasMany(ProviderPortfolio::class, 'provider_id');
    }

    public function verifications()
    {
        return $this->hasMany(ProviderVerification::class, 'provider_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'provider_id');
    }

    public function contactRecords()
    {
        return $this->hasMany(ContactRecord::class, 'provider_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_type' => 'provider'
        ];
    }

    // Recalculate average rating
    public function updateRating()
    {
        $this->rating_avg = $this->reviews()->avg('rating') ?? 0;
        $this->save();
    }
}
