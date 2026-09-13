<?php

namespace App\Enums;

/**
 * Spoločný životný cyklus modelov, ktoré sa schvaľujú alebo zverejňujú.
 * Každý model má ponúkať iba svoju zmysluplnú podmnožinu stavov.
 */
enum ModelStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Rejected = 'rejected';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Archived = 'archived';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Koncept',
            self::PendingReview => 'Čaká na schválenie',
            self::Rejected => 'Zamietnutý',
            self::Scheduled => 'Naplánovaný',
            self::Active => 'Aktívny',
            self::Archived => 'Archivovaný',
            self::Blocked => 'Blokovaný',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return $this === self::Active;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    /** @return array<int, self> */
    public static function options(): array
    {
        return self::cases();
    }

    /** @return array<int, string> */
    public static function publiclyReadableValues(): array
    {
        return [self::Active->value, self::Archived->value];
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
