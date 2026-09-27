<?php

namespace Tests\Unit;

use App\Services\TranscriptHighlights;
use PHPUnit\Framework\TestCase;

class TranscriptHighlightsTest extends TestCase
{
    public function test_selects_a_strong_late_passage_instead_of_the_introduction(): void
    {
        $intro = str_repeat('Vitajte prihláste odber pozdrav ', 100);
        $story = 'Najsilnejší príbeh: prečo odpustiť? Táto skúsenosť mi zmenila život, pretože som pochopil význam zmierenia. ';
        $text = $intro.str_repeat($story, 15);
        $result = (new TranscriptHighlights)->select($text, 800);
        $this->assertStringContainsString('Najsilnejší príbeh', $result);
        $this->assertStringNotContainsString('Vitajte', $result);
        $this->assertLessThanOrEqual(800, strlen($result));
        $this->assertTrue(mb_check_encoding($result, 'UTF-8'));
    }

    public function test_short_transcript_is_preserved_and_tiny_budget_is_safe(): void
    {
        $selector = new TranscriptHighlights;
        $this->assertSame('Celý krátky prepis.', $selector->select('Celý krátky prepis.', 100));
        $this->assertSame('', $selector->select('á', 1));
    }
}
