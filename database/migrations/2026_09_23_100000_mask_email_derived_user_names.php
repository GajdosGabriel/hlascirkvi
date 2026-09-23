<?php

use App\Support\EmailMask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Účty založené komentárom/modlitbou bez registrácie (a Facebook bez mena)
 * dostali ako meno celú časť e-mailu pred zavináčom — a to sa zobrazuje
 * verejne pri komentári. Takéto mená sa nahradia maskovaným tvarom („G•••s“),
 * rovnakým, aký teraz dostávajú nové účty (viď EmailMask::name()).
 *
 * Mení sa len riadok, kde meno presne zodpovedá e-mailu a priezvisko je
 * prázdne — meno, ktoré si používateľ medzitým upravil, ostáva. down() vie
 * pôvodné meno odvodiť z e-mailu späť.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->each(function (object $user) {
            $local = strstr((string) $user->email, '@', true);

            if ($local !== false && mb_strtolower((string) $user->first_name) === mb_strtolower($local)) {
                return $this->maskedName($user->email);
            }

            return null;
        });
    }

    public function down(): void
    {
        $this->each(function (object $user) {
            if ((string) $user->first_name === $this->maskedName($user->email)) {
                return ucfirst((string) strstr((string) $user->email, '@', true));
            }

            return null;
        });
    }

    /** User::setFirstNameAttribute robí ucfirst, tak rovnako aj tu. */
    private function maskedName(?string $email): string
    {
        return ucfirst(EmailMask::name($email));
    }

    /** @param callable(object): ?string $newName */
    private function each(callable $newName): void
    {
        DB::table('users')
            ->select(['id', 'first_name', 'email'])
            ->whereNotNull('email')
            ->where(fn ($q) => $q->whereNull('last_name')->orWhere('last_name', ''))
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($newName) {
                foreach ($users as $user) {
                    $name = $newName($user);

                    if ($name !== null && $name !== $user->first_name) {
                        DB::table('users')->where('id', $user->id)->update(['first_name' => $name]);
                    }
                }
            });
    }
};
