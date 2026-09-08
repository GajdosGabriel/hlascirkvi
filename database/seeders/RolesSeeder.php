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
 * Migrácie balíka v repozitári nie sú, tabuľky nesie database/schema/mysql-schema.sql.
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
