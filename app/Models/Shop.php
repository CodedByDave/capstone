<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Shop extends Model
{

    use HasFactory, Notifiable;
    protected $fillable = [
        'owner_id',
        'shop_name',
        'phone',
        'address',
        'business_license',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
