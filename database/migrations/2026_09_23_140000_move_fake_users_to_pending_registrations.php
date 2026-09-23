<?php

use App\Models\Canal;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * Vymyslené a zjavne zahraničné (spamové) účty z `users` sa presúvajú do
 * `pending_registrations` — tam patria registrácie s nepotvrdenou adresou.
 * Záznam dostane bežnú lehotu PendingRegistration::TTL_DAYS; ak by šlo
 * o skutočného človeka, stačí mu sa znova zaregistrovať a potvrdiť e-mail.
 * Po lehote ho zmaže model:prune.
 *
 * Presúva sa len účet, ktorý:
 *  - nemá overenú adresu,
 *  - nemá inú rolu než `user`,
 *  - po sebe nenechal nič (komentár, obľúbené, uložené, správu, kanál
 *    s obsahom) — preto zostanú napr. ručne založené účty osobností
 *    s vymyslenou adresou, ktoré spravujú kanály s príspevkami,
 *  - a zároveň má zjavne falošnú adresu alebo spamové meno (fakeReason()).
 */
return new class extends Migration
{
    /** Domény, z ktorých sa u nás skutočný užívateľ nikdy nezaregistroval. */
    private const FAKE_DOMAINS = [
        'example.com', 'example.org', 'mail.ru', 'yandex.ru', 'yandex.com', 'bk.ru',
        'list.ru', 'inbox.ru', 'qq.com', 'g.com', 'vymyslenegmail.com', 'brixozu.com',
    ];

    /** Koncovky domén, ktoré tu znamenajú spam (alebo testovaciu adresu). */
    private const FAKE_TLDS = ['ru', 'pw', 'store', 'tst', 'test', 'invalid', 'cn', 'top', 'xyz'];

    public function up(): void
    {
        $users = DB::table('users')
            ->whereNull('email_verified_at')
            ->whereNotExists(fn ($q) => $q->from('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('roles.name', '!=', 'user'))
            ->whereNotExists(fn ($q) => $q->from('comments')->whereColumn('comments.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('favorites')->whereColumn('favorites.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('saved_posts')->whereColumn('saved_posts.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('messengers')->whereColumn('messengers.user_id', 'users.id'))
            ->whereNotExists(fn ($q) => $q->from('canal_user')
                ->whereColumn('canal_user.user_id', 'users.id')
                ->where(fn ($q) => $q
                    ->whereExists(fn ($q) => $q->from('posts')->whereColumn('posts.canal_id', 'canal_user.canal_id'))
                    ->orWhereExists(fn ($q) => $q->from('prayers')->whereColumn('prayers.canal_id', 'canal_user.canal_id'))
                    ->orWhereExists(fn ($q) => $q->from('seminars')->whereColumn('seminars.canal_id', 'canal_user.canal_id'))))
            ->get()
            ->filter(fn ($user) => $this->fakeReason($user) !== null);

        foreach ($users as $user) {
            DB::transaction(fn () => $this->move($user));
        }
    }

    public function down(): void
    {
        // Nevratné — účty (a ich prázdne kanály) sú preč; v pending_registrations
        // ostal len e-mail, meno a heslo do vypršania lehoty.
    }

    private function fakeReason(object $user): ?string
    {
        $email = Str::lower(trim((string) $user->email));
        $domain = Str::after($email, '@');
        $first = (string) $user->first_name;
        $last = (string) $user->last_name;

        return match (true) {
            ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! str_contains($domain, '.') => 'neplatná adresa',
            in_array($domain, self::FAKE_DOMAINS, true) => 'falošná doména',
            in_array(Str::afterLast($domain, '.'), self::FAKE_TLDS, true) => 'zahraničná/spamová doména',
            // Domáce schránky (azet.sk, centrum.cz…) — ľudia tu často zadali
            // e-mail aj ako meno; podľa mena ich nesúdime.
            in_array(Str::afterLast($domain, '.'), ['sk', 'cz'], true) => null,
            // Cyrilika, adresa či odkaz namiesto mena.
            (bool) preg_match('/\p{Cyrillic}|@|https?:|login\d/iu', $first . ' ' . $last) => 'spamové meno',
            // Spamboty: „EdwardsepNG", „BukksAnypeOIXK" — malé písmeno a na konci
            // 2+ veľké, často rovnaké v mene aj priezvisku.
            (bool) preg_match('/\p{Ll}\p{Lu}{2,}$/u', $first) && ($last === '' || $last === $first || preg_match('/\p{Ll}\p{Lu}{2,}$/u', $last)) => 'spamové meno',
            default => null,
        };
    }

    private function move(object $user): void
    {
        DB::table('pending_registrations')->where('email', $user->email)->delete();

        DB::table('pending_registrations')->insert([
            'email' => Str::limit(trim($user->email), 100, ''),
            'first_name' => Str::limit((string) $user->first_name, 50, ''),
            'last_name' => Str::limit((string) $user->last_name, 50, ''),
            'password' => $user->password,
            'token' => null,
            'send_count' => 0,
            'sent_at' => null,
            'expires_at' => now()->addDays(PendingRegistration::TTL_DAYS),
            'created_at' => $user->created_at ?? now(),
            'updated_at' => now(),
        ]);

        // Prázdne osobné kanály, ktoré nikto iný nespravuje (s obsahom sem
        // účet ani neprejde — viď podmienky v up()).
        $canalIds = DB::table('canal_user')->where('user_id', $user->id)->pluck('canal_id');

        foreach ($canalIds as $canalId) {
            $shared = DB::table('canal_user')->where('canal_id', $canalId)->where('user_id', '!=', $user->id)->exists();

            if (! $shared) {
                DB::table('favorites')->where('favorited_type', Canal::class)->where('favorited_id', $canalId)->delete();
                DB::table('buffer_publications')->where('canal_id', $canalId)->delete();
                DB::table('canals')->where('id', $canalId)->delete();
            }
        }

        DB::table('canal_user')->where('user_id', $user->id)->delete();
        DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', User::class)->delete();
        DB::table('model_has_permissions')->where('model_id', $user->id)->where('model_type', User::class)->delete();
        DB::table('notifications')->where('notifiable_id', $user->id)->where('notifiable_type', User::class)->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        DB::table('users')->where('id', $user->id)->delete();
    }
};
