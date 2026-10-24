<?php

namespace App\Enums;

/**
 * Variant loga v hlavnej navigácii. Vyberá ho
 * App\Services\Liturgy\SeasonalLogoPicker podľa dátumu a liturgického roka,
 * kreslí ho šablóna components/navigation/logo.
 */
enum SeasonalLogo: string
{
    /** Čistý nápis — Pôstne obdobie, Veľký piatok a Biela sobota. */
    case Plain = 'plain';

    case Snowy = 'snowy';
    case Snowman = 'snowman';
    case Advent = 'advent';
    case Christmas = 'christmas';
    case Easter = 'easter';
    case Pentecost = 'pentecost';
    case Spring = 'spring';
    case Summer = 'summer';
    case Autumn = 'autumn';

    public function label(): string
    {
        return match ($this) {
            self::Plain => 'Pôvodné',
            self::Snowy => 'Zasnežené',
            self::Snowman => 'Snehuliak',
            self::Advent => 'Advent',
            self::Christmas => 'Vianoce',
            self::Easter => 'Veľká noc',
            self::Pentecost => 'Turíce',
            self::Spring => 'Jar',
            self::Summer => 'Leto',
            self::Autumn => 'Jeseň',
        };
    }
}
