<?php

namespace App\Enums;

enum ShopRegistrationStatus: int
{
    case PENDING = 0;
    case PAID = 1;
    case FAILED = -1;
    
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
        };
    }
}