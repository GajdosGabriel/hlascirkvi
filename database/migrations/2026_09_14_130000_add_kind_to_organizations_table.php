<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Typ kanála (osobnosť / cirkev či spoločenstvo) a koniec ručného poradia
 * v prednom zozname.
 *
 *  - `kind` rozdelí kartu „Kresťanské osobnosti", v ktorej popri kazateľoch
 *    stáli aj ECAV, TKKBS či TV LUX, na dve karty.
 *  - `front_position` zaniká: kartu odteraz radí záujem návštevníkov za
 *    posledné týždne (App\Services\FrontList\FrontList). Ručné poradie by
 *    držalo navrchu stále tých istých.
 */
return new class extends Migration
{
    /**
     * Organizácie, ktorých kontakty boli overené na ich oficiálnych stránkach
     * (migrácia 2026_09_13_120000_enrich_organization_contacts). Podľa názvu,
     * nie podľa ID — to sa mimo produkcie líšiť môže.
     */
    protected const KNOWN_COMMUNITIES = [
        'TKKBS', 'ECAV', 'Apoštolská cirkev Košice', 'Cirkev bratská Prešov',
        'Slovo života Bratislava', 'Sbor Bratrské jednoty baptistů v Aši',
        'Cirkev adventistov s.d.', 'Apoštolská cirkev Bratislava',
        'Koinonia Ján Krstiteľ', 'MPK Slovensko', 'Spoločenstvo Martindom',
        'TV LUX', 'Godzone', 'Komunita Blahoslavenstiev', 'Saleziáni Don Bosca',
        'Slovo+', 'Spoločnosť Božieho Slova / Verbisti', 'EVS',
    ];

    /**
     * Slová, ktoré v názve osobnosti nebývajú. Odhad, nie pravda — výsledok
     * si správca skontroluje v /admin/front-list, kde sa typ prepína jedným
     * tlačidlom.
     */
    protected const COMMUNITY_PATTERN = '~cirk|círk|\bzbor|\bsbor|farnos|spoločenstv|spolocenstv|společenstv'
        . '|komunit|kongregác|rehoľ|rehol|saleziá|verbist|jezuit|františkán|minorit|kapucín|dominikán'
        . '|biskup|diecéz|eparchi|ecav|kbs\b|\btv\b|televízi|rádio|radio|\bfm\b|ministr|church|misi[ae]\b'
        . '|centrum|nadáci|združen|hnuti|\bo\.\s?z\.|univerzit|škol|festival|chrám|bazilik|pútn|slovo života'
        . '|dohovor|po stopách~iu';

    public function up(): void
    {
        if (! Schema::hasTable('organizations') || Schema::hasColumn('organizations', 'kind')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('kind', 20)->nullable()->after('denomination');
        });

        $this->backfill();

        if (Schema::hasColumn('organizations', 'front_position')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropIndex('organizations_front_list_index');
                $table->dropColumn('front_position');
                $table->index(['front_listed_at', 'kind'], 'organizations_front_list_index');
            });
        }
    }

    /**
     * Organizácia podľa zoznamu alebo kľúčového slova. Zvyšok predného
     * zoznamu sú osobnosti — zoznam sa tak volal a väčšinou ním aj bol.
     * Kanály mimo zoznamu bez zhody ostávajú bez typu.
     */
    protected function backfill(): void
    {
        DB::table('organizations')
            ->select(['id', 'title', 'front_listed_at'])
            ->orderBy('id')
            ->chunk(500, function ($canals) {
                foreach ($canals as $canal) {
                    $title = trim((string) $canal->title);

                    $kind = match (true) {
                        in_array($title, self::KNOWN_COMMUNITIES, true),
                        preg_match(self::COMMUNITY_PATTERN, $title) === 1 => 'community',
                        $canal->front_listed_at !== null                   => 'person',
                        default                                            => null,
                    };

                    if ($kind) {
                        DB::table('organizations')->where('id', $canal->id)->update(['kind' => $kind]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organizations') || ! Schema::hasColumn('organizations', 'kind')) {
            return;
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_front_list_index');
            $table->dropColumn('kind');
            $table->unsignedSmallInteger('front_position')->nullable()->after('front_listed_at');
            $table->index(['front_listed_at', 'front_position'], 'organizations_front_list_index');
        });
    }
};
