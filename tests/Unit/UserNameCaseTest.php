<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * User::setFirstNameAttribute/setLastNameAttribute normalizujú meno z
 * formulára (CapsLock, medzery navyše) — pozri App\Models\User::titleCaseName.
 * Meno ide ďalej ako verejný názov osobného kanála (App\Observers\UserObserver)
 * a hľadá sa podľa neho vokatív/rod v `first_names` presnou zhodou.
 */
class UserNameCaseTest extends TestCase
{
    #[Test]
    #[DataProvider('cases')]
    public function first_name_sa_uklada_v_spravnej_velkosti_pismen(string $input, string $expected): void
    {
        $user = new User(['first_name' => $input]);

        $this->assertSame($expected, $user->first_name);
    }

    public static function cases(): array
    {
        return [
            'caps lock' => ['JÁN', 'Ján'],
            'celé malé' => ['ján', 'Ján'],
            'už správne' => ['Ján', 'Ján'],
            'zložené meno' => ['ANNA MÁRIA', 'Anna Mária'],
            'medzery navyše' => ['  ján   ', 'Ján'],
            'pomlčka v priezvisku' => ['kráľová-nová', 'Kráľová-Nová'],
            'apostrof' => ["o'brien", "O'Brien"],
        ];
    }

    #[Test]
    public function last_name_sa_uklada_v_spravnej_velkosti_pismen(): void
    {
        $user = new User(['last_name' => 'NOVÁK']);

        $this->assertSame('Novák', $user->last_name);
    }

    #[Test]
    public function prazdne_meno_ostane_prazdne(): void
    {
        $user = new User(['first_name' => '']);

        $this->assertSame('', $user->first_name);
    }
}
