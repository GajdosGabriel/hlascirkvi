<?php

namespace Tests\Feature;

use Tests\TestCase;

class CanonicalRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['env'] = 'production';
        config(['seo.url' => 'https://hlascirkvi.sk']);
    }

    /** Ako na hostingu: koreňový .htaccess prepíše /public/… na public/index.php. */
    protected function viaPublic(): static
    {
        return $this->withServerVariables([
            'SCRIPT_NAME' => '/public/index.php',
            'SCRIPT_FILENAME' => public_path('index.php'),
            'PHP_SELF' => '/public/index.php',
        ]);
    }

    public function test_adresa_s_public_presmeruje_na_cistu(): void
    {
        $this->viaPublic()
            ->get('https://hlascirkvi.sk/public/feed.xml?x=1')
            ->assertStatus(301)
            ->assertHeader('Location', 'https://hlascirkvi.sk/feed.xml?x=1');
    }

    public function test_adresa_s_public_index_php_presmeruje_na_cistu(): void
    {
        $this->viaPublic()
            ->get('https://hlascirkvi.sk/public/index.php/feed.xml')
            ->assertStatus(301)
            ->assertHeader('Location', 'https://hlascirkvi.sk/feed.xml');
    }

    public function test_www_presmeruje_na_hlavnu_domenu(): void
    {
        $this->get('https://www.hlascirkvi.sk/feed.xml')
            ->assertStatus(301)
            ->assertHeader('Location', 'https://hlascirkvi.sk/feed.xml');
    }

    public function test_cista_adresa_sa_nepresmeruje(): void
    {
        $response = $this->get('https://hlascirkvi.sk/feed.xml');

        $this->assertNotSame(301, $response->getStatusCode());
    }
}
