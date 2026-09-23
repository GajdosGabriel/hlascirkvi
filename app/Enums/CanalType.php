<?php

namespace App\Enums;

/**
 * Ako kanál vznikol — ukladá sa do `canals.type`.
 *
 *  - Personal: osobný kanál, ktorý užívateľ dostane automaticky po overení
 *    e-mailu (App\Services\UserActivation).
 *  - Organization: kanál založený ručne z dashboardu (CanalRequest) alebo
 *    importom z YouTube.
 *
 * Nezamieňať s CanalKind (osobnosť / spoločenstvo), čo je redakčné zaradenie
 * pre predný zoznam.
 */
enum CanalType: string
{
    case Personal = 'personal';
    case Organization = 'organization';

    public function label(): string
    {
        return match ($this) {
            self::Personal     => 'Osobný',
            self::Organization => 'Organizácia',
        };
    }
}
