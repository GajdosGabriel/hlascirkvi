<?php

use App\Support\MediaUrl;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $portraits = [
            359 => ['title' => 'František Trstenský', 'file' => '359.gif'],
            360 => ['title' => 'Mário Tomášik', 'file' => '360.png'],
            361 => ['title' => 'Michal Zamkovský', 'file' => '361.jpg'],
        ];

        foreach ($portraits as $id => $portrait) {
            DB::transaction(function () use ($id, $portrait) {
                $canal = DB::table('canals')->where('id', $id)
                    ->where('title', $portrait['title'])
                    ->where('identity_mode', 'organization')->whereNull('deleted_at')
                    ->where(fn ($query) => $query->whereNull('avatar')->orWhere('avatar', ''))
                    ->lockForUpdate()->first();

                if (! $canal) {
                    return;
                }

                $file = __DIR__.'/../data/canal-person-portraits/'.$portrait['file'];
                $contents = file_get_contents($file);
                if ($contents === false) {
                    throw new RuntimeException('Cannot read canal portrait: '.$file);
                }

                $name = 'portrait-2026-10-02.'.pathinfo($file, PATHINFO_EXTENSION);
                if (! MediaUrl::disk()->put('organizations/'.$id.'/'.$name, $contents)) {
                    throw new RuntimeException('Cannot upload canal portrait: '.$id);
                }

                DB::table('canals')->where('id', $id)->update([
                    'avatar' => $name,
                    'updated_at' => now(),
                ]);
            });
        }
    }

    public function down(): void
    {
        // Forward-only: never delete portraits or later editorial changes.
    }
};
