<?php

namespace App\Services\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalRank;
use App\Enums\ReadingType;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Rozbor HTML liturgického kalendára KBS.
 *
 * Stránka dňa má dve časti: hlavičku (`.lcHEAD` — názov, farba, stupeň,
 * zoznam citácií so žalmovou odpoveďou a aklamáciou) a telo (`.lcBODY` —
 * úvod, nadpis a znenie každého čítania). Citácia v hlavičke a čítanie
 * v tele sa spájajú cez id: `i_c_Jn3_13-17` ↔ `c_Jn3_13-17`.
 *
 * Titulok nadpisu h2 nesie „Cezročné obdobie; lit. rok: 2026; ned./fer.
 * cyklus: A/II" — podľa neho liturgia:overit kontroluje náš výpočet.
 */
class KbsReadingsParser
{
    /**
     * @return array{title: string, rank: ?LiturgicalRank, rank_text: ?string, color: ?LiturgicalColor,
     *               obligation: bool, season_label: ?string, liturgical_year: ?int, cycle: ?string,
     *               sections: array<int, array<string, mixed>>}|null
     */
    public function parseDay(string $html, bool $withTexts = false): ?array
    {
        $xpath = $this->xpath($html);
        $alt = $xpath->query('//div['.$this->cls('lcDENalt').']')->item(0);

        if (! $alt instanceof DOMElement || ! ($header = $this->header($xpath, $alt))) {
            return null;
        }

        $header['sections'] = $this->sections($xpath, $alt, $this->bodies($xpath, $alt, $withTexts));

        return $header;
    }

    /**
     * Mesačný prehľad (?mesiac=RRRRMMDD) — hlavičky všetkých dní. `feria`
     * hovorí, že deň nemá sviatok ani spomienku, názov je teda čisto
     * z kalendára.
     *
     * @return array<string, array<string, mixed>> kľúč RRRR-MM-DD
     */
    public function parseMonth(string $html): array
    {
        $xpath = $this->xpath($html);
        $days = [];

        // Deň obaľuje na stránke dňa <section>, v mesačnom prehľade odkaz <a>.
        foreach ($xpath->query('//*['.$this->cls('lcDEN').'][starts-with(@id, "text_")]') as $section) {
            if (! $section instanceof DOMElement
                || ! preg_match('/text_(\d{4})(\d{2})(\d{2})/', $section->getAttribute('id'), $m)) {
                continue;
            }

            $alt = $xpath->query('.//div['.$this->cls('lcDENalt').']', $section)->item(0);

            if (! $alt instanceof DOMElement || ! ($header = $this->header($xpath, $alt))) {
                continue;
            }

            $header['feria'] = $this->hasClass($section, 'feria');
            $days["{$m[1]}-{$m[2]}-{$m[3]}"] = $header;
        }

        return $days;
    }

    /** @return array<string, mixed>|null */
    protected function header(DOMXPath $xpath, DOMElement $alt): ?array
    {
        $h2 = $xpath->query('.//h2', $alt)->item(0);

        if (! $h2 instanceof DOMElement) {
            return null;
        }

        preg_match(
            '~^\s*(.*?)\s*;\s*lit\.\s*rok:\s*(\d{4})\s*;\s*ned\./fer\.\s*cyklus:\s*([ABC]/I{1,2})~u',
            $h2->getAttribute('title'),
            $meta
        );

        $color = $xpath->query('.//span['.$this->cls('lcFARBA').']', $h2)->item(0);
        $type = $xpath->query('.//i['.$this->cls('lcTYP').']', $h2)->item(0);

        $rankText = $type ? trim($this->text($type), ' ()') : '';
        $title = $this->textExcept($h2, ['lcFARBA', 'lcTYP']);
        $obligation = false;

        // „PRIKÁZANÁ SLÁVNOSŤ: Narodenie Pána"
        if (preg_match('/^([\p{Lu}\s]+):\s*(.+)$/u', $title, $prefix)) {
            $obligation = str_contains($prefix[1], 'PRIKÁZAN');
            $title = $prefix[2];
        }

        return [
            'title' => $title,
            'rank' => $rankText !== '' ? LiturgicalRank::fromKbs($rankText) : null,
            'rank_text' => $rankText !== '' ? $rankText : null,
            'color' => $color instanceof DOMElement
                ? LiturgicalColor::fromKbs($color->getAttribute('title') ?: $this->text($color))
                : null,
            'obligation' => $obligation,
            'season_label' => $meta[1] ?? null,
            'liturgical_year' => isset($meta[2]) ? (int) $meta[2] : null,
            'cycle' => $meta[3] ?? null,
        ];
    }

    /**
     * Omše dňa z hlavičky. Nadpis h3 (Vo svätej noci, Na úsvite…) začína
     * novú omšu, riadok `.lcLINE` je jedno čítanie aj s alternatívami.
     *
     * @param  array<string, array<string, ?string>>  $bodies
     * @return array<int, array<string, mixed>>
     */
    protected function sections(DOMXPath $xpath, DOMElement $alt, array $bodies): array
    {
        $head = $xpath->query('.//div['.$this->cls('lcHEAD').']', $alt)->item(0);

        if (! $head instanceof DOMElement) {
            return [];
        }

        $sections = [];
        $current = null;
        $nodes = $xpath->query(
            './/h3['.$this->cls('lcSEKCIAtitul').'] | .//div['.$this->cls('lcLINE').']',
            $head
        );

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($node->nodeName === 'h3') {
                if ($current !== null) {
                    $sections[] = $current;
                }

                $current = ['title' => $this->text($node) ?: null, 'lines' => []];

                continue;
            }

            $current ??= ['title' => null, 'lines' => []];

            if ($line = $this->line($xpath, $node, $bodies)) {
                $current['lines'][] = $line;
            }
        }

        if ($current !== null) {
            $sections[] = $current;
        }

        return array_values(array_filter($sections, fn (array $section) => $section['lines'] !== []));
    }

    /**
     * @param  array<string, array<string, ?string>>  $bodies
     * @return array<string, mixed>|null
     */
    protected function line(DOMXPath $xpath, DOMElement $line, array $bodies): ?array
    {
        $options = [];
        $label = null;
        $type = null;
        $response = null;
        $acclamation = null;

        $nodes = $xpath->query(
            './div['.$this->cls('lcCITANIE').'] | ./*['.$this->cls('lcMOD').']',
            $line
        );

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            // „alebo", „alebo kratšie", „alebo večer" medzi dvomi možnosťami.
            if ($this->hasClass($node, 'lcMOD')) {
                $label = $this->text($node) ?: null;

                continue;
            }

            $citation = $xpath->query('.//p['.$this->cls('lcSUR').']', $node)->item(0);

            if (! $citation instanceof DOMElement) {
                continue;
            }

            $intro = $this->clean($citation->getAttribute('title'));
            $input = $xpath->query('.//input[@id]', $citation)->item(0);
            $key = $input instanceof DOMElement ? preg_replace('/^i_/', '', $input->getAttribute('id')) : null;
            $body = $key !== null ? ($bodies[$key] ?? []) : [];

            $type ??= ReadingType::fromKbs($intro);
            $response ??= $this->optionalText($xpath, './/p['.$this->cls('lcRESPblock').']//span['.$this->cls('lcVERS').']', $node);
            $acclamation ??= $this->optionalText($xpath, './/p['.$this->cls('lcVPEblock').']//span['.$this->cls('lcVERS').']', $node);

            $options[] = [
                'label' => $options === [] ? null : $label,
                'citation' => $this->text($citation),
                'intro' => $intro,
                'heading' => $body['heading'] ?? null,
                'bible_url' => $body['bible_url'] ?? null,
                'text' => $body['text'] ?? null,
            ];

            $label = null;
        }

        if ($options === [] || $type === null) {
            return null;
        }

        return [
            'type' => $type->value,
            'response' => $response,
            'acclamation' => $acclamation,
            'options' => $options,
        ];
    }

    /**
     * Nadpis, odkaz do Biblie a (na požiadanie) znenie každého čítania z tela
     * stránky, podľa id.
     *
     * @return array<string, array<string, ?string>>
     */
    protected function bodies(DOMXPath $xpath, DOMElement $alt, bool $withTexts): array
    {
        $bodies = [];
        $nodes = $xpath->query(
            './/div['.$this->cls('lcBODY').']//div['.$this->cls('lcCITANIE').'][@id]',
            $alt
        );

        foreach ($nodes as $div) {
            if (! $div instanceof DOMElement || isset($bodies[$div->getAttribute('id')])) {
                continue;
            }

            $heading = $xpath->query('.//h5', $div)->item(0);
            $link = $xpath->query('.//h4//a[@href]', $div)->item(0);

            $bodies[$div->getAttribute('id')] = [
                'heading' => $heading ? ($this->text($heading) ?: null) : null,
                'bible_url' => $link instanceof DOMElement ? $link->getAttribute('href') : null,
                'text' => $withTexts ? $this->bodyText($div) : null,
            ];
        }

        return $bodies;
    }

    /** Znenie čítania: odseky oddelené prázdnym riadkom, verše žalmu po riadkoch. */
    protected function bodyText(DOMElement $div): ?string
    {
        $parts = [];

        foreach ($div->childNodes as $child) {
            if (! $child instanceof DOMElement
                || in_array($child->nodeName, ['h4', 'h5'], true)
                || $this->hasClass($child, 'dovetok')
                || $this->hasClass($child, 'lcRESPblock')
                || $this->hasClass($child, 'lcVPEblock')
                || $this->hasClass($child, 'lcMOD')) {
                continue;
            }

            $lines = array_map(fn (string $line) => $this->clean($line), explode("\n", $this->rawText($child, [], true)));
            $text = trim(implode("\n", array_filter($lines, fn (string $line) => $line !== '')));

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return $parts === [] ? null : implode("\n\n", $parts);
    }

    protected function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        // Bez deklarácie kódovania by libxml čítal UTF-8 ako Latin-1.
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_COMPACT);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($dom);
    }

    /** XPath podmienka „element má triedu" (celé slovo, nie podreťazec). */
    protected function cls(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    protected function hasClass(DOMNode $node, string $class): bool
    {
        return $node instanceof DOMElement
            && in_array($class, preg_split('/\s+/', trim($node->getAttribute('class'))) ?: [], true);
    }

    protected function text(DOMNode $node): string
    {
        return $this->clean($node->textContent);
    }

    protected function optionalText(DOMXPath $xpath, string $expression, DOMNode $context): ?string
    {
        $node = $xpath->query($expression, $context)->item(0);
        $text = $node ? $this->text($node) : '';

        return $text !== '' ? $text : null;
    }

    /** @param array<int, string> $skipClasses */
    protected function textExcept(DOMNode $node, array $skipClasses): string
    {
        return $this->clean($this->rawText($node, $skipClasses));
    }

    /** @param array<int, string> $skipClasses */
    protected function rawText(DOMNode $node, array $skipClasses, bool $breaks = false): string
    {
        $out = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $out .= $child->nodeValue;
            } elseif ($child instanceof DOMElement) {
                if ($child->nodeName === 'br') {
                    $out .= $breaks ? "\n" : ' ';
                } elseif (! array_filter($skipClasses, fn (string $class) => $this->hasClass($child, $class))) {
                    $out .= $this->rawText($child, $skipClasses, $breaks);
                }
            }
        }

        return $out;
    }

    /** Nedeliteľné a úzke medzery, tabulátory aj zalomenia → jedna medzera. */
    protected function clean(string $text): string
    {
        return trim((string) preg_replace('/[\s\x{00A0}\x{2009}\x{202F}]+/u', ' ', $text));
    }
}
