<?php

namespace Tests\Unit\Liturgy;

use App\Enums\LiturgicalColor;
use App\Enums\LiturgicalRank;
use App\Services\Liturgy\KbsReadingsParser;
use PHPUnit\Framework\TestCase;

/**
 * Rozbor skutočných stránok liturgického kalendára KBS (tests/Fixtures/kbs).
 */
class KbsReadingsParserTest extends TestCase
{
    private function parse(string $file, bool $withTexts = false): array
    {
        $day = (new KbsReadingsParser)->parseDay($this->fixture($file), $withTexts);
        $this->assertNotNull($day);

        return $day;
    }

    private function fixture(string $file): string
    {
        return file_get_contents(__DIR__.'/../../Fixtures/kbs/'.$file);
    }

    public function test_sviatok_s_alternativnym_citanim(): void
    {
        $day = $this->parse('day-2026-09-14.html');

        $this->assertSame('Povýšenie Svätého kríža', $day['title']);
        $this->assertSame(LiturgicalRank::Feast, $day['rank']);
        $this->assertSame(LiturgicalColor::Red, $day['color']);
        $this->assertSame('Cezročné obdobie', $day['season_label']);
        $this->assertSame(2026, $day['liturgical_year']);
        $this->assertSame('A/II', $day['cycle']);
        $this->assertFalse($day['obligation']);

        $this->assertCount(1, $day['sections']);
        [$first, $psalm, $gospel] = $day['sections'][0]['lines'];

        $this->assertSame('reading', $first['type']);
        $this->assertSame('Nm 21, 4c-9', $first['options'][0]['citation']);
        $this->assertNull($first['options'][0]['label']);
        $this->assertSame('Ak sa pohryzený pozrie naň, ostane nažive', $first['options'][0]['heading']);
        $this->assertSame('alebo', $first['options'][1]['label']);
        $this->assertSame('Flp 2, 6-11', $first['options'][1]['citation']);

        $this->assertSame('psalm', $psalm['type']);
        $this->assertSame('Pane, ty buď našou spásou.', $psalm['response']);

        $this->assertSame('gospel', $gospel['type']);
        $this->assertSame('Jn 3, 13-17', $gospel['options'][0]['citation']);
        $this->assertSame('Čítanie zo svätého Evanjelia podľa Jána', $gospel['options'][0]['intro']);
        $this->assertSame('Syn človeka musí byť vyzdvihnutý', $gospel['options'][0]['heading']);
        $this->assertStringContainsString('Jn3,13', $gospel['options'][0]['bible_url']);
        $this->assertStringStartsWith('Klaniame sa ti, Kriste', $gospel['acclamation']);

        // Plné znenie sa bez požiadania neberie.
        $this->assertNull($gospel['options'][0]['text']);
    }

    public function test_plne_znenie_na_poziadanie(): void
    {
        $day = $this->parse('day-2026-09-14.html', withTexts: true);
        [, $psalm, $gospel] = $day['sections'][0]['lines'];

        $this->assertStringStartsWith('Ježiš povedal Nikodémovi', $gospel['options'][0]['text']);
        $this->assertStringNotContainsString('Počuli sme', $gospel['options'][0]['text']);
        $this->assertStringContainsString("Počúvaj, ľud môj, moju náuku, *\nnakloň sluch", $psalm['options'][0]['text']);
    }

    public function test_nedela_bez_sviatku(): void
    {
        $day = $this->parse('day-2026-09-20.html');

        $this->assertSame('25. nedeľa v Cezročnom období', $day['title']);
        $this->assertNull($day['rank']);
        $this->assertSame(LiturgicalColor::Green, $day['color']);
        $this->assertSame(
            ['Iz 55, 6-9', 'Ž 145, 2-3. 8-9. 17-18', 'Flp 1, 20c-24. 27a', 'Mt 20, 1-16'],
            array_map(fn ($line) => $line['options'][0]['citation'], $day['sections'][0]['lines'])
        );
    }

    public function test_viac_omsi_a_prikazany_sviatok(): void
    {
        $day = $this->parse('day-2026-12-25.html');

        $this->assertStringContainsString('Narodenie Pána', $day['title']);
        $this->assertStringNotContainsString('PRIKÁZAN', $day['title']);
        $this->assertTrue($day['obligation']);
        $this->assertSame(LiturgicalRank::Solemnity, $day['rank']);
        $this->assertSame('B/I', $day['cycle']);

        $this->assertSame(
            ['Vo svätej noci', 'Na úsvite', 'Počas dňa'],
            array_column($day['sections'], 'title')
        );

        $gospel = end($day['sections'][2]['lines']);
        $this->assertSame('Jn 1, 1-18', $gospel['options'][0]['citation']);
        $this->assertSame('alebo kratšie', $gospel['options'][1]['label']);
    }

    public function test_html_entity_a_sekvencia_na_velku_noc(): void
    {
        $day = $this->parse('day-2026-04-05.html');

        $this->assertSame('Veľkonočné trojdnie', $day['season_label']);
        $this->assertSame(LiturgicalColor::White, $day['color']);

        $lines = $day['sections'][0]['lines'];
        $this->assertSame('Čítanie zo Skutkov Apoštolov', $lines[0]['options'][0]['intro']);
        $this->assertSame('Sk 10, 34a. 37-43', $lines[0]['options'][0]['citation']);
        $this->assertContains('sequence', array_column($lines, 'type'));

        $gospel = end($lines);
        $this->assertSame([null, 'alebo', 'alebo večer'], array_column($gospel['options'], 'label'));
    }

    public function test_poznamka_namiesto_stupna_slavenia(): void
    {
        $day = $this->parse('day-2026-02-18.html');

        $this->assertNull($day['rank']);
        $this->assertStringStartsWith('Pôst a zdržovanie sa mäsitého pokrmu', $day['rank_text']);
        $this->assertSame(LiturgicalColor::Violet, $day['color']);
        $this->assertSame('2 Kor 5, 20 – 6, 2', $day['sections'][0]['lines'][2]['options'][0]['citation']);
    }

    public function test_lubovolna_spomienka_s_viacerymi_svatymi(): void
    {
        $day = $this->parse('day-2026-09-17.html');

        $this->assertSame(LiturgicalRank::OptionalMemorial, $day['rank']);
        $this->assertStringContainsString('Róberta Bellarmína', $day['title']);
        $this->assertStringContainsString('alebo Svätej Hildegardy', $day['title']);
    }

    public function test_mesacny_prehlad(): void
    {
        $days = (new KbsReadingsParser)->parseMonth($this->fixture('month-2026-09.html'));

        $this->assertCount(30, $days);
        $this->assertSame('25. nedeľa v Cezročnom období', $days['2026-09-20']['title']);
        $this->assertTrue($days['2026-09-20']['feria']);
        $this->assertFalse($days['2026-09-14']['feria']);
        $this->assertSame('A/II', $days['2026-09-01']['cycle']);
    }

    public function test_neznama_stranka(): void
    {
        $this->assertNull((new KbsReadingsParser)->parseDay('<html><body>Údržba</body></html>'));
    }
}
