<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Shop extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'owner_id',
        'shop_name',
        'phone',
        'municipality',
        'barangay',
        'block_street',
        'postal_code',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }
}

