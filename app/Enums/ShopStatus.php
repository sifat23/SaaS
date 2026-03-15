<?php

namespace App\Enums;

enum ShopStatus: int
{
    case ACTIVE = 1;
    case SUSPENDED = 0;
    case CANCEL = -1;

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::CANCEL => 'Cancel',
        };
    }
}