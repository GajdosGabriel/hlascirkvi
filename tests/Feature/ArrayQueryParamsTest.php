<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Roboty a skenery posielajú parametre aj ako polia (?search[]=x). Verejná
 * stránka na to nesmie odpovedať 500-kou — pole sa má ignorovať alebo odmietnuť.
 */
class ArrayQueryParamsTest extends TestCase
{
    use RefreshDatabase;

    public static function urls(): array
    {
        return [
            'úvod' => ['/?search[]=x&page[]=2&trends[]=1'],
            'api posts' => ['/api/posts?per_page[]=5&limit[]=5&search[]=x'],
            'akcie' => ['/akcie?search[]=x&municipality[]=y&list[]=past&view[]=mapa&range[]=weekend&page[]=2'],
            'akcie tags' => ['/akcie?tags[0][]=x'],
            'konferencie' => ['/konferencie-a-pute?q[]=x&year[]=2020'],
            'modlitby' => ['/modlitby?search[]=x'],
            'api prayers' => ['/api/prayers?page[]=2'],
            'osobnosti' => ['/osobnosti?search[]=x'],
            'citania' => ['/citania?datum[]=x'],
            'zamyslenia' => ['/zamyslenia?page[]=1'],
        ];
    }

    #[DataProvider('urls')]
    public function test_array_parameters_do_not_crash_public_pages(string $url): void
    {
        Http::fake(['*' => Http::response(['data' => [], 'meta' => []], 200)]);

        $status = $this->get($url)->getStatusCode();

        $this->assertLessThan(500, $status, "{$url} vrátilo {$status}");
    }
}
