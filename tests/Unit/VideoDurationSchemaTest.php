<?php

namespace Tests\Unit;

use App\Support\Seo;
use PHPUnit\Framework\TestCase;

class VideoDurationSchemaTest extends TestCase
{
    public function test_unknown_zero_negative_and_invalid_durations_are_omitted(): void
    {
        foreach ([null, '', 'PT0S', 'PT0H0M0S', 'P0D', '-PT1S', 'PT-1S', 'invalid', '12:03', 'P', 'PT'] as $value) {
            $this->assertNull(Seo::videoDuration($value), var_export($value, true));
        }
    }

    public function test_positive_iso_durations_are_preserved(): void
    {
        foreach (['PT1S', 'PT12M3S', 'PT1H30M', 'P1D', 'PT25H', 'P1W'] as $value) {
            $this->assertSame($value, Seo::videoDuration($value));
        }
    }
}