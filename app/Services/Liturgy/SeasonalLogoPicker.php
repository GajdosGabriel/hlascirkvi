<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalSeason;
use App\Enums\SeasonalLogo;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Ktoré logo patrí do navigácie v daný deň.
 *
 * Sviatočné obdobia majú prednosť pred ročnými:
 *  - Advent (1. adventná nedeľa – 23. 12.), na veniec horí toľko sviečok,
 *    koľký je adventný týždeň.
 *  - Vianoce od Štedrého dňa po Krst Krista Pána.
 *  - Pôst, Veľký piatok a Biela sobota bez ozdôb.
 *  - Veľká noc od Veľkonočnej nedele po Nedeľu Božieho milosrdenstva.
 *  - Turíce počas novény k Duchu Svätému (od piatku po Nanebovstúpení)
 *    po Zoslanie Ducha Svätého.
 *
 * Zvyšok roka podľa meteorologických období: december a január
 * zasnežené, február snehuliak, potom jar, leto a jeseň po troch mesiacoch.
 */
final class SeasonalLogoPicker
{
    public function __construct(private LiturgicalCalendar $calendar) {}

    /** @return array{logo: SeasonalLogo, candles: int} */
    public function for(CarbonInterface|string $date): array
    {
        $day = $this->calendar->for($date);
        $date = $day->date;
        $easter = Computus::easter($date->year);
        $pentecost = $easter->addDays(49);

        $logo = match (true) {
            $date->month === 12 && $date->day === 24 => SeasonalLogo::Christmas,
            $day->season === LiturgicalSeason::Advent => SeasonalLogo::Advent,
            $day->season === LiturgicalSeason::Christmas => SeasonalLogo::Christmas,
            $day->season === LiturgicalSeason::Lent => SeasonalLogo::Plain,
            $this->between($date, $easter, $easter->addDays(7)) => SeasonalLogo::Easter,
            $day->season === LiturgicalSeason::Triduum => SeasonalLogo::Plain,
            $this->between($date, $pentecost->subDays(9), $pentecost) => SeasonalLogo::Pentecost,
            default => match ($date->month) {
                12, 1 => SeasonalLogo::Snowy,
                2 => SeasonalLogo::Snowman,
                3, 4, 5 => SeasonalLogo::Spring,
                6, 7, 8 => SeasonalLogo::Summer,
                default => SeasonalLogo::Autumn,
            },
        };

        return [
            'logo' => $logo,
            'candles' => $logo === SeasonalLogo::Advent ? min($day->week, 4) : 0,
        ];
    }

    private function between(CarbonImmutable $date, CarbonImmutable $from, CarbonImmutable $to): bool
    {
        $ymd = $date->format('Y-m-d');

        return $ymd >= $from->format('Y-m-d') && $ymd <= $to->format('Y-m-d');
    }
}
