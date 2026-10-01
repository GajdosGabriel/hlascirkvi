<?php

use App\Support\MediaUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Logá (monogramy) organizácií a fotky osôb, ktoré kanál nemal. Súbory sú
     * v database/data/canal-avatars/{id}.jpg a nahrávajú sa na disk `images.disk`
     * (na ostrom S3) pod organizations/{id}/{slug}.jpg. Existujúci avatar sa
     * nikdy neprepisuje a migrácia sa dá bezpečne spustiť opakovane.
     */
    public function up(): void
    {
        $disk = MediaUrl::disk();

        foreach (glob(__DIR__.'/../data/canal-avatars/*.jpg') as $file) {
            $id = (int) basename($file, '.jpg');

            $canal = DB::table('canals')->where('id', $id)
                ->where('identity_mode', 'organization')->whereNull('deleted_at')
                ->where(fn ($q) => $q->whereNull('avatar')->orWhere('avatar', ''))
                ->first();

            if (! $canal) {
                continue;
            }

            $name = ($canal->slug ?: 'avatar-'.$id).'.jpg';

            $disk->put('organizations/'.$id.'/'.$name, file_get_contents($file));

            DB::table('canals')->where('id', $id)->update(['avatar' => $name, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Forward-only: súbory a avatary už mohol vlastník zmeniť.
    }
};
