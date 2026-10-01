<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as bootApplication;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Test nikdy nesmie siahnuť na skutočné API (YouTube, KBS…) — žiadna
        // kvóta ani sieť; potrebné odpovede si test falšuje cez Http::fake.
        Http::preventStrayRequests();
    }

    /** Pečiatka ľudského formulára (\App\Support\HumanCheck) staršia než minimálny čas. */
    protected function humanStamp(): string
    {
        return \Illuminate\Support\Facades\Crypt::encryptString((string) (time() - 60));
    }

    /**
     * Poistka pred zmazaním vývojovej databázy. Pri uloženej konfigurácii
     * (bootstrap/cache/config.php) Laravel ignoruje DB_DATABASE z phpunit.xml
     * a RefreshDatabase by vymazal databázu, nad ktorou beží web. Kontrola je
     * pred setUpTraits, teda skôr, než sa RefreshDatabase dotkne databázy.
     */
    public function createApplication()
    {
        $app = $this->bootApplication();

        $connection = $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Testy by bežali nad databázou „{$database}“, nie nad testovacou (*_test). "
                . 'Spusti `php artisan config:clear` a skús znova.'
            );
        }

        return $app;
    }
}
