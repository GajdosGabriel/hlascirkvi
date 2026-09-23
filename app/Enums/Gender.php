<?php

namespace App\Enums;

/**
 * Pohlavie užívateľa v `users.gender`. Dopĺňa ho UserObserver podľa krstného
 * mena (tabuľka first_names); null = nevieme.
 */
enum Gender: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male   => 'Muž',
            self::Female => 'Žena',
        };
    }
}
