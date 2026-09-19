<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aktívny kanál (users.canal_id) smie byť len kanál, ktorý užívateľ spravuje
 * (canal_user). Policy `manage` do 9/2026 uznávala aj samotný canal_id, takže
 * sa rozdiel nikde neprejavil — teraz sa zosúladí:
 *
 * - kanál bez jediného správcu patrí tomu, kto ho má aktívny (staré osobné
 *   kanály spred zavedenia pivotu) — užívateľ sa doplní do správcov;
 * - kanál s inými správcami užívateľovi nepatrí — aktívny kanál sa prepne
 *   na iný jeho kanál, prípadne ostane prázdny.
 */
return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')
            ->whereNotNull('canal_id')
            ->whereNotExists(fn ($q) => $q->from('canal_user')
                ->whereColumn('canal_user.user_id', 'users.id')
                ->whereColumn('canal_user.canal_id', 'users.canal_id'))
            ->get(['id', 'canal_id']);

        foreach ($users as $user) {
            $hasManagers = DB::table('canal_user')->where('canal_id', $user->canal_id)->exists();
            $canalExists = DB::table('canals')->where('id', $user->canal_id)->exists();

            if ($canalExists && ! $hasManagers) {
                DB::table('canal_user')->insert([
                    'canal_id' => $user->canal_id,
                    'user_id' => $user->id,
                ]);

                continue;
            }

            DB::table('users')->where('id', $user->id)->update([
                'canal_id' => DB::table('canal_user')->where('user_id', $user->id)->min('canal_id'),
            ]);
        }
    }

    public function down(): void
    {
        // Pôvodný stav bol nekonzistentný, nie je dôvod ho obnovovať.
    }
};
