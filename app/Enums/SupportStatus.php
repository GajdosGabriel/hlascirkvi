<?php

namespace App\Enums;

/**
 * Na kom je vlákno podpory.
 *
 * `open` = čaká na správcu, `answered` = správca odpovedal a čaká sa na
 * používateľa. Bez toho rozlíšenia by správca nevedel, čo ešte nikto nevybavil.
 */
enum SupportStatus: string
{
    case Open = 'open';
    case Answered = 'answered';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Čaká na odpoveď',
            self::Answered => 'Zodpovedané',
            self::Closed => 'Uzavreté',
        };
    }

    /** Tón pre x-dashboard.status-badge. */
    public function tone(): string
    {
        return match ($this) {
            self::Open => 'amber',
            self::Answered => 'green',
            self::Closed => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
