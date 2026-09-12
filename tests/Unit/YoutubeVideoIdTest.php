<?php

namespace Tests\Unit;

use App\Services\Youtube\VideoId;
use PHPUnit\Framework\TestCase;

class YoutubeVideoIdTest extends TestCase
{
    public function test_vytiahne_id_z_vysledku_vyhladavania(): void
    {
        $item = json_decode(json_encode([
            'id' => ['kind' => 'youtube#video', 'videoId' => 'dQw4w9WgXcQ'],
        ]));

        $this->assertSame('dQw4w9WgXcQ', VideoId::from($item));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function itemWithoutVideoProvider(): array
    {
        return [
            // Presne tvar, na ktorom padal denný import kanála.
            'kanál medzi výsledkami' => [['id' => ['kind' => 'youtube#channel', 'channelId' => 'UCuAXFkgsw1L7xaCfnd5JJOw']]],
            'playlist medzi výsledkami' => [['id' => ['kind' => 'youtube#playlist', 'playlistId' => 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI']]],
            'id bez videoId' => [['id' => ['kind' => 'youtube#video']]],
            'položka bez id' => [['snippet' => ['title' => 'Bez ID']]],
        ];
    }

    /**
     * @param array<string, mixed> $item
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('itemWithoutVideoProvider')]
    public function test_polozka_bez_videa_vrati_null(array $item): void
    {
        $this->assertNull(VideoId::from(json_decode(json_encode($item))));
    }

    public function test_zvladne_aj_ostatne_tvary_odpovede(): void
    {
        $upload = json_decode(json_encode([
            'contentDetails' => ['upload' => ['videoId' => 'abcdefghijk']],
        ]));
        $playlistItem = json_decode(json_encode([
            'snippet' => ['resourceId' => ['kind' => 'youtube#video', 'videoId' => 'lmnopqrstuv']],
        ]));
        $videosList = json_decode(json_encode(['id' => 'wxyz01234_-']));

        $this->assertSame('abcdefghijk', VideoId::from($upload));
        $this->assertSame('lmnopqrstuv', VideoId::from($playlistItem));
        $this->assertSame('wxyz01234_-', VideoId::from($videosList));
    }

    public function test_neplatny_vstup_vrati_null(): void
    {
        $this->assertNull(VideoId::from(null));
        $this->assertNull(VideoId::from('dQw4w9WgXcQ'));
        // Kratší reťazec nie je ID videa.
        $this->assertNull(VideoId::from(json_decode(json_encode(['id' => 'UCshort']))));
    }
}
