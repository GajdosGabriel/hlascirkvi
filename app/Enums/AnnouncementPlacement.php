<?php

namespace App\Enums;

/**
 * Miesta, kam sa dá oznam zavesiť. Hodnota sa ukladá do `announcements.placement`
 * a šablóna si oznamy vypýta cez <x-announcements placement="…" />.
 */
enum AnnouncementPlacement: string
{
    /** Pruh pod hlavným menu — vidno ho na každej stránke webu. */
    case Top = 'top';

    /** Panel na úvodnej stránke nad výpisom príspevkov. */
    case Home = 'home';

    /** Modul v bočnom paneli úvodnej stránky, medzi kartami. */
    case Sidebar = 'sidebar';

    /** Nástenka správcu kanála a administrácia — oznam len pre prihlásených. */
    case Dashboard = 'dashboard';

    /** Pás nad pätičkou, opäť na každej stránke. */
    case Footer = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::Top       => 'Pruh pod menu (celý web)',
            self::Home      => 'Úvodná stránka nad príspevkami',
            self::Sidebar   => 'Bočný panel úvodnej stránky',
            self::Dashboard => 'Nástenka správcu kanála',
            self::Footer    => 'Pás nad pätičkou (celý web)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Top       => 'Úzky farebný pruh hneď pod hlavným menu. Vidí ho každý návštevník na každej stránke, preto sem patria len krátke a dôležité oznamy.',
            self::Home      => 'Panel na titulnej stránke nad výpisom príspevkov. Unesie aj dlhší text a odkaz.',
            self::Sidebar   => 'Karta v bočnom paneli titulnej stránky vedľa modlitieb a zamyslení.',
            self::Dashboard => 'Oznam pre správcov kanálov — zobrazí sa im v nástenke a v administrácii, návštevníci ho nevidia.',
            self::Footer    => 'Pás nad pätičkou. Vhodné na trvalejšie oznamy, ktoré nemusia byť hneď navrchu.',
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
