<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Doplní verejné kontaktné údaje overené na oficiálnych stránkach organizácií.
 *
 * Migrácia zámerne vypĺňa iba prázdne hodnoty (pri ulici aj pôvodné zástupné
 * texty „Neuvedené“ a „neznáme“). Ručné zmeny urobené na produkcii preto
 * neprepíše. ID je vždy kontrolované aj názvom, aby sa údaje nemohli priradiť
 * inému záznamu v databáze s odlišným obsahom.
 */
return new class extends Migration
{
    /**
     * Zdroje boli overené 13. 9. 2026. URL pri každom riadku je oficiálna
     * stránka alebo jej kontaktná podstránka.
     */
    private const ORGANIZATIONS = [
        101 => [
            'title' => 'TKKBS',
            'url_www' => 'https://tkkbs.sk',
            'source' => 'https://tkkbs.sk/',
        ],
        102 => [
            'title' => 'ECAV',
            'village' => 'Bratislava',
            'street' => 'Palisády 46',
            'psc' => 81106,
            'email' => 'ecav@ecav.sk',
            'phone' => '+421 2 592 012 20',
            'url_www' => 'https://www.ecav.sk',
            'source' => 'https://www.ecav.sk/generalny-biskupsky-urad',
        ],
        258 => [
            'title' => 'Apoštolská cirkev Košice',
            'village' => 'Košice',
            'street' => 'Bratislavská 1',
            'psc' => 4011,
            'email' => 'kosice@acsr.sk',
            'url_www' => 'https://ackosice.sk',
            'source' => 'https://ackosice.sk/kontakt/',
        ],
        259 => [
            'title' => 'Cirkev bratská Prešov',
            'village' => 'Prešov',
            'street' => 'Slovenská 34',
            'psc' => 8001,
            'email' => 'presov@cb.sk',
            'phone' => '+421 51 772 5995',
            'url_www' => 'https://www.cbpo.sk',
            'source' => 'https://www.cb.sk/index.php/kontakt/zbory/18-presov',
        ],
        260 => [
            'title' => 'Slovo života Bratislava',
            'village' => 'Bratislava',
            'street' => 'Tomášikova 30B',
            'psc' => 82101,
            'email' => 'slovozivota@slovozivota.sk',
            'phone' => '+421 2 209 293 11',
            'url_www' => 'https://www.slovozivota.sk',
            'source' => 'https://www.slovozivota.sk/',
        ],
        261 => [
            'title' => 'Sbor Bratrské jednoty baptistů v Aši',
            'street' => 'Bratrská 2960/40',
            'psc' => 35201,
            'email' => 'bjbas@seznam.cz',
            'phone' => '+420 723 224 082',
            'url_www' => 'https://bjbas.cz',
            'source' => 'https://bjbas.cz/kontakt',
        ],
        262 => [
            'title' => 'Cirkev adventistov s.d.',
            'village' => 'Bratislava',
            'street' => 'Cablkova 3',
            'psc' => 82104,
            'email' => 'casd@casd.sk',
            'phone' => '+421 905 400 052',
            'url_www' => 'https://adventisti.sk',
            'source' => 'https://adventisti.sk/',
        ],
        266 => [
            'title' => 'Apoštolská cirkev Bratislava',
            'village' => 'Bratislava',
            'street' => 'Sreznevského 2',
            'psc' => 83103,
            'email' => 'office@acsr.sk',
            'phone' => '+421 948 592 562',
            'url_www' => 'https://www.acsr.sk',
            'source' => 'https://www.acsr.sk/kontakt/',
        ],
        276 => [
            'title' => 'Koinonia Ján Krstiteľ',
            'street' => 'Vyšný Klátov 300',
            'psc' => 4412,
            'email' => 'vysnyklatov@koinonia.sk',
            'phone' => '+421 904 738 091',
            'url_www' => 'https://koinonia.sk',
            'source' => 'https://koinonia.sk/vstup/',
        ],
        283 => [
            'title' => 'MPK Slovensko',
            'street' => 'Svarín 400',
            'url_www' => 'https://www.mpks.sk',
            'source' => 'https://www.mpks.sk/kontakt',
        ],
        287 => [
            'title' => 'Spoločenstvo Martindom',
            'village' => 'Bratislava',
            'street' => 'Rožňavská 17',
            'psc' => 83104,
            'email' => 'infocentrum@martindom.sk',
            'phone' => '+421 2 555 713 97',
            'url_www' => 'https://www.martindom.sk',
            'source' => 'https://www.martindom.sk/kontakt/',
        ],
        358 => [
            'title' => 'TV LUX',
            'village' => 'Bratislava',
            'street' => 'Prepoštská 5',
            'psc' => 81101,
            'email' => 'tvlux@tvlux.sk',
            'phone' => '+421 2 212 955 55',
            'url_www' => 'https://www.tvlux.sk',
            'source' => 'https://www.tvlux.sk/kontakt',
        ],
        371 => [
            'title' => 'Godzone',
            'village' => 'Sliač',
            'street' => 'Jarná 13',
            'psc' => 96231,
            'email' => 'godzone@godzone.sk',
            'phone' => '+421 944 537 274',
            'url_www' => 'https://godzone.sk',
            'source' => 'https://godzone.sk/partnerska-podpora/',
        ],
        465 => [
            'title' => 'Komunita Blahoslavenstiev',
            'village' => 'Liptovský Mikuláš',
            'street' => 'Kláštorná 123',
            'psc' => 3104,
            'email' => 'blahoslavenstva@gmail.com',
            'phone' => '+421 948 447 425',
            'url_www' => 'https://blahoslavenstva.sk',
            'source' => 'https://blahoslavenstva.sk/sk/o-nas/kontakt',
        ],
        478 => [
            'title' => 'Saleziáni Don Bosca',
            'village' => 'Bratislava',
            'street' => 'Miletičova 7',
            'psc' => 82108,
            'email' => 'misie@saleziani.sk',
            'phone' => '+421 903 960 212',
            'url_www' => 'https://saleziani.sk',
            'source' => 'https://saleziani.sk/misie/kontakty',
        ],
        642 => [
            'title' => 'Slovo+',
            'village' => 'Piešťany',
            'street' => 'Winterova 1752/10',
            'psc' => 92101,
            'phone' => '+421 948 028 474',
            'url_www' => 'https://www.slovoplus.sk',
            'source' => 'https://www.slovoplus.sk/redakcia',
        ],
        661 => [
            'title' => 'Spoločnosť Božieho Slova / Verbisti',
            'village' => 'Bratislava',
            'street' => 'Krupinská 2',
            'psc' => 85101,
            'phone' => '+421 2 635 349 51',
            'url_www' => 'https://www.verbisti.sk',
            'source' => 'https://www.verbisti.sk/kontakty/',
        ],
        755 => [
            'title' => 'EVS',
            'village' => 'Bratislava',
            'street' => 'Legionárska 4',
            'psc' => 81499,
            'email' => 'lydia@evs.sk',
            'phone' => '+421 911 798 800',
            'url_www' => 'https://evs.sk',
            'source' => 'https://evs.sk/',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('organizations')) {
            return;
        }

        $nationalVillageId = Schema::hasTable('villages')
            ? DB::table('villages')->where('fullname', 'Celé Slovensko')->value('id')
            : null;

        foreach (self::ORGANIZATIONS as $id => $data) {
            $title = $data['title'];
            unset($data['title'], $data['source']);

            $village = $data['village'] ?? null;
            unset($data['village']);

            foreach ($data as $column => $value) {
                $query = DB::table('organizations')->where('id', $id)->where('title', $title);

                if ($column === 'street') {
                    $query->where(function ($query) {
                        $query->whereNull('street')->orWhere('street', '')->orWhereIn('street', ['Neuvedené', 'neznáme']);
                    });
                } else {
                    $query->where(function ($query) use ($column) {
                        $query->whereNull($column)->orWhere($column, '');
                    });
                }

                $values = [$column => $value, 'updated_at' => now()];
                if ($column === 'phone') {
                    $values['phone_numeric'] = preg_replace('/\D+/', '', $value);
                }

                $query->update($values);
            }

            if ($village !== null && $nationalVillageId !== null) {
                $villageId = DB::table('villages')->where('fullname', $village)->value('id');

                if ($villageId !== null) {
                    DB::table('organizations')
                        ->where('id', $id)
                        ->where('title', $title)
                        ->where('village_id', $nationalVillageId)
                        ->update(['village_id' => $villageId, 'updated_at' => now()]);
                }
            }
        }
    }

    /**
     * Verejné kontakty sa pri rollbacku nemažú: medzičasom ich môže správca
     * potvrdiť alebo upraviť a deštruktívny rollback by tak zmazal platné dáta.
     */
    public function down(): void
    {
        // Data-only migration; intentionally irreversible.
    }
};
