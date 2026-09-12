<?php

namespace App\Services\Youtube;

use App\Models\User;
use App\Notifications\Admin\YoutubeImportIssue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Chyby importu videí, ktoré vie správca opraviť v administrácii, posiela
 * superadminovi do zvončeka namiesto zápisu do logu — v logu si ich nikto
 * nevšimol a kanál tak ostal roky s nefunkčným zdrojom.
 *
 * V logu zostávajú len technické zlyhania (vyčerpaná kvóta, výpadok YouTube
 * API), s ktorými sa v administrácii nedá nič urobiť.
 */
class NotifyAdmin
{
    /**
     * Rozposlané dvojice kanál + druh chyby. Import prechádza stovky kanálov
     * v jednom behu, takže sa neprečítané hlásenia načítajú raz a nie dopytom
     * pri každom kanáli.
     *
     * @var array<string, true>|null
     */
    private static ?array $pending = null;

    public static function about($canal, string $key, string $message): void
    {
        $token = $canal->id . '|' . $key;

        // Import beží denne — kým hlásenie visí neprečítané, to isté
        // neposielame znova. Po prečítaní (teda po oprave alebo vedomom
        // odložení) môže prísť nové.
        if (isset(self::pending()[$token])) {
            return;
        }

        self::$pending[$token] = true;

        Notification::send(
            self::superadmins(),
            new YoutubeImportIssue($canal, $key, Str::limit($message, 240))
        );
    }

    /**
     * Opravu rieši správa obsahu, nie vlastník kanála — tomu by ostalo len
     * neriešiteľné hlásenie.
     */
    public static function superadmins()
    {
        return User::role('superadmin')->get();
    }

    /**
     * @return array<string, true>
     */
    private static function pending(): array
    {
        if (self::$pending !== null) {
            return self::$pending;
        }

        self::$pending = [];

        // `notifications.data` je v tejto databáze text, nie json, takže sa
        // dvojica číta v PHP a nie cez json_extract. Neprečítaných hlásení
        // tohto druhu je rádovo toľko, koľko je pokazených kanálov.
        DB::table('notifications')
            ->where('type', YoutubeImportIssue::class)
            ->whereNull('read_at')
            ->pluck('data')
            ->each(function ($data) {
                $data = json_decode((string) $data, true);

                if (isset($data['canal_id'], $data['key'])) {
                    self::$pending[$data['canal_id'] . '|' . $data['key']] = true;
                }
            });

        return self::$pending;
    }

    /**
     * Beh testu (aj dlhobežiaci worker) nesmie dediť zoznam z predchádzajúceho.
     */
    public static function forget(): void
    {
        self::$pending = null;
    }
}
