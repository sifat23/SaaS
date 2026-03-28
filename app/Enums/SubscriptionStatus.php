<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIALING = 'trialing';
    case PAST_DUE = 'past_due';
    case UNPAID = 'unpaid';
    case CANCELED = 'canceled';
    case INCOMPLETE = 'incomplete';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::TRIALING => 'Trialing',
            self::PAST_DUE => 'Past Due',
            self::UNPAID => 'Unpaid',
            self::CANCELED => 'Canceled',
            self::INCOMPLETE => 'Incomplete',
            self::INCOMPLETE_EXPIRED => 'Incomplete Expired',
            self::PAUSED => 'Paused',
        };
    }
}
