<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    // Role zo spatie/laravel-permission, predtým ich zakladal RolesSeeder.
    // Bez nich sa nedá založiť ani jeden užívateľ — UserObserver::created volá
    // assignRole('user') a na chýbajúcej role hodí RoleDoesNotExist. Na
    // produkcii už role sú, preto sa dopĺňajú len chýbajúce.
    private const ROLES = ['superadmin', 'admin', 'editor', 'publisher', 'user'];

    public function up(): void
    {
        foreach (self::ROLES as $name) {
            $exists = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->exists();
            if (! $exists) {
                DB::table('roles')->insert([
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Role sa nemažú — visia na nich priradenia existujúcich účtov.
    }
};
