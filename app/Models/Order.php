<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_name',
        'shop_name',
        'owner_name',
        'email',
        'phone',
        'block_street',
        'municipality',
        'barangay',
        'postal_code',
        'modules',
        'status',
        'total_price',
    ];

    protected $casts = [
        'modules' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
