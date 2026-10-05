<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Overené cez playlists.list oficiálneho kanála UC2hBgEp6D1fno_PwcQyJlBA, 4. 10. 2026.
        // BHD 2026 má iba promo/Shorts; tie nenahrádzajú playlist prednášok.
        $playlists = [
            2016 => 'PLCu4owYx2YSR7CKHIDsppChwUU-_UUzq8',
            2017 => 'PLCu4owYx2YSQ9ADHwRv6aLRva9iOXpLal',
            2018 => 'PLCu4owYx2YST0Z_U-cbiTLHtPtIabr1I5',
            2019 => 'PLCu4owYx2YSSZBqLbKWuvqbzpRAOk-xAt',
            2020 => 'PLCu4owYx2YSSsNGgfSNlOa3w29Z1_OYRN',
            2021 => 'PLCu4owYx2YSSVG0_936CaCHmdX_sWp5U-',
            2022 => 'PLCu4owYx2YSSmx7vOurCftD1mAjJWreoF',
            2023 => 'PLCu4owYx2YSTV5CBymgvp9Vg5vPRHrAFj',
            2024 => 'PLCu4owYx2YSQ0lfMz9boCaOO7CkYQKhxZ',
            2025 => 'PLCu4owYx2YSSBN0W-Dg2cCbLy6F6ugPGg',
        ];

        DB::transaction(function () use ($playlists) {
            foreach ($playlists as $year => $playlist) {
                DB::table('seminars')->whereNull('deleted_at')
                    ->whereIn('title', ["Bratislavské Hanusove dni {$year}", "Bratislavské Hanusove Dni {$year}"])
                    ->where(function ($query) use ($year, $playlist) {
                        $query->whereNull('youtube_playlist')->orWhere('youtube_playlist', '')
                            ->orWhere('youtube_playlist', $playlist);
                        if ($year === 2019) {
                            $query->orWhere('youtube_playlist', 'PLCu4owYx2YST29lvJlfZF7hg4Lga1Fdk2');
                        }
                    })->update(['youtube_playlist' => $playlist, 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        // Overené zdroje ponechávame; rollback nesmie prepísať následné ručné úpravy.
    }
};
