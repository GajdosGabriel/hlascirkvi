<?php

namespace App\Console\Commands;

use App\Models\Canal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Upratanie starých účtov s nikdy nepotvrdenou adresou, ktoré po sebe
 * nenechali žiadnu stopu — typicky robotické registrácie spred zavedenia
 * App\Models\PendingRegistration. S účtom zmizne aj jeho prázdny osobný kanál.
 *
 * Bez --force len vypíše, čo by zmazal.
 */
class PruneUnverifiedUsers extends Command
{
    protected $signature = 'users:prune-unverified
        {--days=30 : Účet musí byť starší ako toľko dní}
        {--force : Naozaj zmazať (inak len výpis)}';

    protected $description = 'Delete old unverified users without any activity (and their empty personal canals)';

    public function handle(): int
    {
        $days = max(7, (int) $this->option('days'));

        $users = $this->candidates($days)->with('canals')->get();

        if ($users->isEmpty()) {
            $this->info('Žiadne účty na zmazanie.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Meno', 'E-mail', 'Založený', 'Kanály'], $users->map(fn (User $u) => [
            $u->id,
            $u->fullname,
            $u->email,
            $u->created_at?->format('d.m.Y'),
            $u->canals->pluck('title')->implode(', '),
        ]));

        if (! $this->option('force')) {
            $this->warn(sprintf('%d účtov by sa zmazalo. Spustite s --force.', $users->count()));

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                foreach ($user->canals as $canal) {
                    if ($this->canalIsEmpty($canal, $user)) {
                        $canal->forceDelete();
                    }
                }

                $user->roles()->detach();
                $user->notifications()->delete();
                // forceDelete — mäkko zmazaný riadok by adresu naďalej blokoval
                // pre skutočnú registráciu (unique:users).
                $user->forceDelete();
            });

            $deleted++;
        }

        $this->info(sprintf('Zmazaných %d neoverených účtov.', $deleted));

        return self::SUCCESS;
    }

    /** @return Builder<User> */
    protected function candidates(int $days): Builder
    {
        return User::query()
            ->whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays($days))
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', '!=', 'user'))
            ->whereDoesntHave('commentss', fn ($q) => $q->withTrashed())
            ->whereNotExists(fn ($q) => $q->from('favorites')->whereColumn('favorites.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('saved_posts')->whereColumn('saved_posts.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('messengers')->whereColumn('messengers.user_id', 'users.id'))
            // Žiadny spravovaný kanál s obsahom.
            ->whereNotExists(fn ($q) => $q->from('canal_user')
                ->whereColumn('canal_user.user_id', 'users.id')
                ->where(fn ($q) => $q
                    ->whereExists(fn ($q) => $q->from('posts')->whereColumn('posts.canal_id', 'canal_user.canal_id'))
                    ->orWhereExists(fn ($q) => $q->from('prayers')->whereColumn('prayers.canal_id', 'canal_user.canal_id'))
                    ->orWhereExists(fn ($q) => $q->from('seminars')->whereColumn('seminars.canal_id', 'canal_user.canal_id'))
                ));
    }

    /**
     * Kanál sa zmaže len vtedy, keď ho nespravuje nikto iný a nikto si ho
     * neoznačil ako obľúbený.
     */
    protected function canalIsEmpty(Canal $canal, User $user): bool
    {
        return ! DB::table('canal_user')->where('canal_id', $canal->id)->where('user_id', '!=', $user->id)->exists()
            && ! DB::table('posts')->where('canal_id', $canal->id)->exists()
            && ! DB::table('prayers')->where('canal_id', $canal->id)->exists()
            && ! DB::table('seminars')->where('canal_id', $canal->id)->exists()
            && ! DB::table('buffer_publications')->where('canal_id', $canal->id)->exists()
            && ! DB::table('favorites')->where('favorited_type', $canal->getMorphClass())->where('favorited_id', $canal->id)->exists();
    }
}
