<?php

namespace Tests\Feature;

use App\Support\Seo;
use Tests\TestCase;

class SeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'seo.url' => 'https://hlascirkvi.sk',
            'seo.site_name' => 'Hlas Cirkvi',
            'seo.title' => 'Predvolený titulok',
            'seo.description' => 'Predvolený popis webu.',
            'seo.image' => '/images/og-default.jpg',
            'seo.image_width' => 1200,
            'seo.image_height' => 630,
        ]);
    }

    public function test_prazdne_pole_vrati_predvolene_hodnoty_webu(): void
    {
        $meta = Seo::resolve();

        $this->assertSame('Predvolený titulok | Hlas Cirkvi', $meta['document_title']);
        $this->assertSame('Predvolený popis webu.', $meta['description']);
        $this->assertSame('https://hlascirkvi.sk/images/og-default.jpg', $meta['image']);
        // Rozmery patria k predvolenému obrázku, preto ich pozná.
        $this->assertSame(1200, $meta['image_width']);
        $this->assertSame(630, $meta['image_height']);
    }

    public function test_meno_webu_sa_v_titulku_neopakuje(): void
    {
        $meta = Seo::resolve(['title' => 'Novinky na Hlas Cirkvi']);

        $this->assertSame('Novinky na Hlas Cirkvi', $meta['document_title']);
    }

    public function test_popis_je_bez_znaciek_a_orezany_na_dve_dlzky(): void
    {
        $body = '<p>Prvý  odsek s <strong>HTML</strong> a entitou &amp;.</p>' . str_repeat(' slovo', 100);

        $meta = Seo::resolve(['description' => $body]);

        $this->assertStringStartsWith('Prvý odsek s HTML a entitou &.', $meta['description']);
        $this->assertStringNotContainsString('<', $meta['description']);
        // 160 znakov pre výsledok vyhľadávania, 200 pre náhľad odkazu.
        $this->assertLessThanOrEqual(163, mb_strlen($meta['description']));
        $this->assertGreaterThan(mb_strlen($meta['description']), mb_strlen($meta['og_description']));
    }

    public function test_kanonicka_adresa_ide_vzdy_na_jednu_domenu(): void
    {
        $meta = Seo::resolve(['canonical' => 'http://www.hlascirkvi.local/post/1/slug?page=2']);

        $this->assertSame('https://hlascirkvi.sk/post/1/slug?page=2', $meta['canonical']);
    }

    public function test_kanonicka_adresa_vypisu_zahodi_sledovacie_parametre(): void
    {
        $this->app->instance('request', \Illuminate\Http\Request::create(
            'http://hlascirkvi.local/akcie?fbclid=abc&utm_source=fb&municipality=nitra&view=mapa&page=3'
        ));

        $this->assertSame(
            'http://hlascirkvi.local/akcie?municipality=nitra&page=3',
            Seo::listUrl(['list', 'municipality'], 3)
        );
        $this->assertSame('http://hlascirkvi.local/akcie', Seo::listUrl([], 1));
        $this->assertTrue(Seo::hasQuery(['view']));
        $this->assertFalse(Seo::hasQuery(['trends']));
    }

    public function test_obrazok_z_cudzieho_uloziska_ostava_nedotknuty(): void
    {
        $meta = Seo::resolve(['image' => 'https://i.ytimg.com/vi/abc/hqdefault.jpg']);

        $this->assertSame('https://i.ytimg.com/vi/abc/hqdefault.jpg', $meta['image']);
        // Rozmery cudzieho obrázka nepoznáme a nesmieme podsunúť tie predvolené.
        $this->assertNull($meta['image_width']);
    }

    public function test_noindex_sa_da_vynutit_zo_sablony(): void
    {
        $this->assertSame('noindex, nofollow', Seo::resolve(['noindex' => true])['robots']);
        $this->assertStringStartsWith('index, follow', Seo::resolve(['noindex' => false])['robots']);
    }

    /**
     * Disallow v robots.txt robotovi zakáže stránku stiahnuť, takže jej
     * noindex neuvidí a zaindexovanú adresu nechá v indexe („Indexovaná,
     * ale zablokovaná súborom robots.txt"). Adresy s noindex preto zakázané
     * byť nesmú.
     */
    public function test_robots_txt_nezakazuje_adresy_s_noindex(): void
    {
        preg_match_all('/^Disallow:\s*(\S+)/mi', file_get_contents(public_path('robots.txt')), $rules);

        $paths = ['/login', '/register', '/password/reset', '/admin/home', '/dashboard',
            '/?search=x', '/post?page=2&search=x', '/akcie', '/akcie?tags=put', '/akcie/12/omsa'];

        foreach ($paths as $path) {
            foreach ($rules[1] as $rule) {
                $pattern = '#^' . str_replace('\*', '.*', preg_quote($rule, '#')) . '#';

                $this->assertDoesNotMatchRegularExpression($pattern, $path, "robots.txt ($rule) zakazuje $path");
            }
        }
    }

    public function test_podujatia_sa_indexuju_na_hlascirkvi(): void
    {
        foreach (['akcie.index', 'event.show'] as $name) {
            $this->assertFalse(\Illuminate\Support\Str::is(config('seo.noindex_routes'), $name), $name);
        }
    }

    public function test_drobceky_maju_poradie_a_absolutne_adresy(): void
    {
        $schema = Seo::breadcrumbs([
            ['Hlas Cirkvi', '/'],
            ['Kanál', '/organizations/1'],
            ['Príspevok', null],
        ]);

        $this->assertSame('BreadcrumbList', $schema['@type']);
        $this->assertSame([1, 2, 3], array_column($schema['itemListElement'], 'position'));
        $this->assertSame('https://hlascirkvi.sk/organizations/1', $schema['itemListElement'][1]['item']);
        // Posledná položka je aktuálna stránka, adresu mať nemusí.
        $this->assertArrayNotHasKey('item', $schema['itemListElement'][2]);
    }
}
