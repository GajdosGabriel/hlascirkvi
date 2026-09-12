<?php

namespace Tests\Unit;

use App\Services\Youtube\ChannelId;
use App\Services\Youtube\PlaylistId;
use PHPUnit\Framework\TestCase;

/**
 * Rozoberanie vstupu bez dopytu na YouTube API — presne tá časť, ktorá
 * rozhoduje, či sa do `channelId` dostane adresa kanála (403 od YouTube)
 * alebo skutočné ID.
 */
class YoutubeIdTest extends TestCase
{
    public function testChannelIdPassesThrough()
    {
        $this->assertSame(
            'UCtWheHmWwuokUASxXus4NBw',
            ChannelId::fromInput('  UCtWheHmWwuokUASxXus4NBw ')
        );
    }

    public function testChannelIdIsCutFromUrl()
    {
        $this->assertSame(
            'UCtWheHmWwuokUASxXus4NBw',
            ChannelId::fromInput('https://www.youtube.com/channel/UCtWheHmWwuokUASxXus4NBw/videos')
        );
    }

    public function testAddressWithHandleIsNotAnId()
    {
        // Presne hodnota, na ktorej import padal.
        $this->assertNull(ChannelId::fromInput('https://www.youtube.com/@EVSchcemviac'));
        $this->assertNull(ChannelId::fromInput(''));
        $this->assertNull(ChannelId::fromInput(null));
    }

    public function testHandleIsReadFromInput()
    {
        foreach ($this->handles() as [$input, $expected]) {
            $this->assertSame($expected, ChannelId::handleFromInput($input), 'vstup: ' . var_export($input, true));
        }
    }

    private function handles(): array
    {
        return [
            ['https://www.youtube.com/@EVSchcemviac', 'EVSchcemviac'],
            ['www.youtube.com/@fatimatv9954/streams', 'fatimatv9954'],
            ['https://youtube.com/c/NejakyKanal', 'NejakyKanal'],
            ['https://www.youtube.com/user/StaryKanal', 'StaryKanal'],
            ['@EVSchcemviac', 'EVSchcemviac'],
            ['EVSchcemviac', 'EVSchcemviac'],
            // Ani z adresy videa, ani z ID kanála sa handle robiť nemá.
            ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', null],
            ['https://www.youtube.com/channel/UCtWheHmWwuokUASxXus4NBw', null],
            ['UCtWheHmWwuokUASxXus4NBw', null],
            ['https://vimeo.com/@nieyoutube', null],
            ['', null],
        ];
    }

    public function testPlaylistId()
    {
        $this->assertSame(
            'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
            PlaylistId::fromInput('PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU')
        );

        $this->assertSame(
            'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
            PlaylistId::fromInput('https://www.youtube.com/playlist?list=PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU')
        );

        $this->assertSame(
            'PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU',
            PlaylistId::fromInput('https://www.youtube.com/watch?v=dQw4w9WgXcQ&list=PLe026qiswQ_HMnmxNZ9IBBpO2ytIcP9HU')
        );

        $this->assertNull(PlaylistId::fromInput('https://www.youtube.com/@EVSchcemviac'));
        $this->assertNull(PlaylistId::fromInput('nejaky text'));
    }
}
