<?php

namespace App\Enums;

/**
 * Druh čítania v bohoslužbe slova. Prvé a druhé čítanie sa nerozlišujú
 * typom, ale poradím — Veľkonočná vigília ich má sedem.
 */
enum ReadingType: string
{
    case Reading = 'reading';
    case Psalm = 'psalm';
    case Sequence = 'sequence';
    case Gospel = 'gospel';

    public function label(): string
    {
        return match ($this) {
            self::Reading => 'Čítanie',
            self::Psalm => 'Žalm',
            self::Sequence => 'Sekvencia',
            self::Gospel => 'Evanjelium',
        };
    }

    /** Z titulku citácie na stránke KBS („Responzóriový žalm", „Čítanie zo svätého Evanjelia…"). */
    public static function fromKbs(string $intro): self
    {
        $intro = mb_strtolower($intro);

        return match (true) {
            str_contains($intro, 'žalm') => self::Psalm,
            str_contains($intro, 'sekvencia') => self::Sequence,
            str_contains($intro, 'evanjeli') => self::Gospel,
            default => self::Reading,
        };
    }

    /** @return array<int, self> */
    public static function options(): array
    {
        return self::cases();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
