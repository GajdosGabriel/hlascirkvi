<?php

namespace App\Enums;

/** O čom používateľ píše podpore — podľa toho sa vlákno v schránke triedi. */
enum SupportCategory: string
{
    case Question = 'question';
    case Problem = 'problem';
    case Canal = 'canal';
    case Idea = 'idea';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Question => 'Otázka',
            self::Problem => 'Niečo nefunguje',
            self::Canal => 'Kanál a YouTube',
            self::Idea => 'Návrh / podnet',
            self::Other => 'Iné',
        };
    }

    /** @return array<string, string> hodnota => popis, pre <select> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
