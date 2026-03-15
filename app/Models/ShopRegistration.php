<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRegistration extends Model
{
    protected $fillable = ['email', 'shop_name', 'stripe_session_id', 'status'];
}
