<?php

namespace App\Services\Youtube;

use App\Notifications\Admin\YoutubeSourceMissing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Kanál, ktorého zdroj videí na YouTube už neexistuje, sa z denného importu
 * vyradí — inak by to isté 403 padalo do logu každý deň dookola. O vypnutí sa
 * dozvie superadmin notifikáciou; späť sa import zapne sám, keď správca
 * do kanála zapíše nové ID (App\Http\Controllers\Canal\CanalController).
 */
class DisableImport
{
    public static function because($canal, string $reason): void
    {
        // Dvakrát to isté kanálu nevypíname, aby sa notifikácia neopakovala.
        if ($canal->youtube_disabled_at !== null) {
            return;
        }

        $reason = Str::limit($reason, 180);

        $canal->forceFill([
            'youtube_disabled_at' => now(),
            'youtube_disabled_reason' => $reason,
        ])->save();

        Log::warning('Sťahovanie videí z YouTube vypnuté: ' . $reason, [
            'organization_id' => $canal->id,
            'channel' => $canal->youtube_channel,
            'playlist' => $canal->youtube_playlist,
        ]);

        Notification::send(NotifyAdmin::superadmins(), new YoutubeSourceMissing($canal, $reason));
    }
}
