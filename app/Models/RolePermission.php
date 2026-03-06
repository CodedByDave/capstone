<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'shop_id',
        'role',
        'module',
        'action'
    ];
}
