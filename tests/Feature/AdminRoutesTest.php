<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Routy administrácie a nástenky musia mať v kontroleri svoju metódu.
 *
 * `Route::resource()` zaregistruje sedem rout aj kontroleru, ktorý má jedinú
 * (výpis). Také adresy sa nedajú odlíšiť od funkčných — v menu ani vo výpise
 * na ne odkaz nevedie, ale kto na ne trafí (odkaz z e-mailu, záložka, ručne
 * dopísané /create), dostane 500: „Method ... does not exist".
 *
 * Verejná časť a API majú rovnaké zvyšky, tie sú na samostatné upratanie —
 * tento test stráži to, čo je za prihlásením.
 */
class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_kazda_routa_administracie_ma_svoju_metodu(): void
    {
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();

            if (! str_starts_with($name, 'admin.') && ! str_starts_with($name, 'profile.')) {
                continue;
            }

            $action = $route->getActionName();

            if (! str_contains($action, '@')) {
                continue;
            }

            [$controller, $method] = explode('@', $action);

            if (class_exists($controller) && method_exists($controller, $method)) {
                continue;
            }

            $missing[] = $name.' → '.class_basename($controller).'@'.$method;
        }

        $this->assertSame([], $missing, "Routy bez metódy v kontroleri:\n".implode("\n", $missing));
    }

    public function test_superadmin_otvori_vsetky_hlavne_stranky_administracie(): void
    {
        $this->seed(RolesSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);

        $routes = [
            'admin.announcement.index',
            'admin.announcement.create',
            'admin.buffer.index',
            'admin.canal.index',
            'admin.comment.index',
            'admin.frontlist.index',
            'admin.home.index',
            'admin.image.index',
            'admin.post.index',
            'admin.prayer.index',
            'admin.statistic.index',
            'admin.user.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk("Administračná stránka {$route} sa nedá otvoriť.");
        }
    }
}
