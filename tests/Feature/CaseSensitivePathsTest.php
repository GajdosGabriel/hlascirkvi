<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Cesty musia sedieť aj vo veľkosti písmen.
 *
 * Vývoj beží na Windows, kde je jedno, či sa priečinok volá `navigation`
 * alebo `Navigation` — súbor sa nájde tak či tak. Produkčný Linux je citlivý,
 * takže trieda sa jednoducho nenačíta. Pri komponentoch to nie je ani vidieť:
 * Blade bez triedy vykreslí pohľad ako anonymný komponent a stránka spadne až
 * na prvej premennej, ktorú mala doplniť trieda („Undefined variable $menu"
 * zhodilo celú administráciu aj nástenku).
 *
 * Test preto porovnáva mená súborov tak, ako ich vracia scandir().
 */
class CaseSensitivePathsTest extends TestCase
{
    public function test_namespace_sedi_s_priecinkom_a_trieda_s_menom_suboru(): void
    {
        $problems = [];

        foreach ($this->phpFiles(app_path()) as $file) {
            $source = file_get_contents($file);
            $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);

            if (preg_match('/^namespace\s+([^;]+);/m', $source, $m)) {
                $expected = 'App\\' . str_replace(
                    '/', '\\',
                    trim(str_replace(app_path(), '', dirname($file)), '/\\')
                );
                $expected = rtrim($expected, '\\');

                if (trim($m[1]) !== $expected) {
                    $problems[] = $relative . ': namespace ' . trim($m[1]) . ', priečinok ' . $expected;
                }
            }

            if (preg_match('/^(?:final\s+|abstract\s+)?(?:class|interface|trait|enum)\s+(\w+)/m', $source, $c)
                && $c[1] !== basename($file, '.php')) {
                $problems[] = $relative . ': trieda ' . $c[1] . ', súbor ' . basename($file);
            }
        }

        $this->assertSame([], $problems, "Nesúlad veľkosti písmen:\n" . implode("\n", $problems));
    }

    /**
     * Každá značka <x-…> musí mať buď pohľad, alebo triedu — a to presne
     * v tej veľkosti písmen, akú z nej Blade odvodí.
     */
    public function test_komponenty_v_sablonach_sa_daju_najst(): void
    {
        $problems = [];

        foreach ($this->phpFiles(resource_path('views')) as $file) {
            if (! preg_match_all('/<x-([a-z0-9][a-zA-Z0-9._\-]*)/', file_get_contents($file), $m)) {
                continue;
            }

            foreach (array_unique($m[1]) as $tag) {
                if (str_starts_with($tag, 'slot')) {
                    continue;
                }

                $view = resource_path('views/components/' . str_replace('.', '/', $tag) . '.blade.php');

                $class = app_path('View/Components/' . implode('/', array_map(
                    fn ($segment) => str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $segment))),
                    explode('.', $tag)
                )) . '.php');

                if (! $this->existsExact($view) && ! $this->existsExact($class)) {
                    $problems[] = 'x-' . $tag . ' (' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file) . ')';
                }
            }
        }

        $this->assertSame([], $problems, "Komponenty bez pohľadu aj bez triedy:\n" . implode("\n", $problems));
    }

    /** @return array<int, string> */
    private function phpFiles(string $directory): array
    {
        $files = [];

        foreach (scandir($directory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $files = array_merge($files, $this->phpFiles($path));
            } elseif (str_ends_with($entry, '.php')) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /** file_exists() na Windows veľkosť písmen ignoruje, scandir() nie. */
    private function existsExact(string $path): bool
    {
        $parts = preg_split('#[\\\\/]#', str_replace(base_path(), '', $path), -1, PREG_SPLIT_NO_EMPTY);
        $current = base_path();

        foreach ($parts as $part) {
            $entries = @scandir($current);

            if ($entries === false || ! in_array($part, $entries, true)) {
                return false;
            }

            $current .= DIRECTORY_SEPARATOR . $part;
        }

        return true;
    }
}
