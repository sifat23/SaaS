<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopRegistration extends Model
{
    protected $fillable = ['owner_name', 'password', 'owner_email', 'shop_name', 'stripe_session_id', 'status'];
}
