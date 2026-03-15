<?php

namespace App\Models;

use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $casts = [
        'status' => ShopStatus::class,
    ];
    protected $fillable = ['name', 'slug', 'email', 'owner_id', 'phone', 'address', 'status', 'trial_ends_at'];


}
