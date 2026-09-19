<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Role zo spatie/laravel-permission. Bez nich sa nedá založiť ani jeden
 * užívateľ — App\Observers\UserObserver::created volá assignRole('user')
 * a notifikáciu adminom cez scope role('admin'), a oboje na chýbajúcej role
 * hodí RoleDoesNotExist.
 *
 * Tabuľky balíka zakladá migrácia 0001_01_01_000008_create_permission_tables.
 */
class RolesSeeder extends Seeder
{
    public const ROLES = ['superadmin', 'admin', 'editor', 'publisher', 'user'];

    public function run()
    {
        foreach (self::ROLES as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
