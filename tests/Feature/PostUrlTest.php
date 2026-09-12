<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PostUrlTest extends TestCase
{
    /**
     * Príspevky s prázdnym slugom (názov bez písmen latinky dá prázdny
     * Str::slug) zhadzovali celý výpis kariet: route() nechal {slug}
     * nenahradený a vyhodil UrlGenerationException. Nepovinný segment
     * z nich urobí obyčajnú adresu bez slugu.
     */
    public function test_post_url_is_built_even_without_slug(): void
    {
        $this->assertSame(url('/post/7'), route('post.show', [7, '']));
        $this->assertSame(url('/post/7'), route('post.show', [7, null]));
        $this->assertSame(url('/post/7/moj-clanok'), route('post.show', [7, 'moj-clanok']));
    }

    public function test_detail_route_accepts_address_without_slug(): void
    {
        $routes = Route::getRoutes();

        $this->assertNotNull($routes->match(
            \Illuminate\Http\Request::create('/post/7', 'GET')
        ));
        $this->assertSame('post.show', $routes->match(
            \Illuminate\Http\Request::create('/post/7', 'GET')
        )->getName());
    }
}
