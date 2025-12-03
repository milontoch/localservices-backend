<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'document_url',
        'status',
        'notes',
    ];

    public function provider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }
}
