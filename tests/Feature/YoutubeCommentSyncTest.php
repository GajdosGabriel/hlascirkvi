<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\Youtube\CommentSync;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakesYoutube;
use Tests\TestCase;

class YoutubeCommentSyncTest extends TestCase
{
    use RefreshDatabase, FakesYoutube;

    private const VIDEO = 'video000001';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        // Zástupný používateľ komentárov z YouTube.
        User::factory()->create(['id' => CommentSync::USER_ID]);
    }

    private function videoPost(array $attributes = []): Post
    {
        return Post::factory()->create($attributes + ['video_id' => self::VIDEO]);
    }

    private function comment(string $id, string $text, array $snippet = []): array
    {
        return [
            'id' => $id,
            'snippet' => $snippet + [
                'textOriginal' => $text,
                'textDisplay' => $text,
                'authorDisplayName' => '@Pisatel' . $id,
                'authorProfileImageUrl' => 'https://yt3.ggpht.com/avatar-' . $id,
                'publishedAt' => '2026-09-01T10:00:00Z',
            ],
        ];
    }

    private function fakeThreads(array $threads, int $commentCount = 5): void
    {
        $this->fakeYoutube([
            'videos' => $this->videosFrom([
                self::VIDEO => $this->videoResource(self::VIDEO, ['statistics' => ['commentCount' => (string) $commentCount]]),
            ]),
            'commentThreads' => ['items' => $threads],
        ]);
    }

    public function test_skips_abusive_threads_and_replies(): void
    {
        $this->videoPost();
        $this->fakeThreads([
            ['snippet' => ['topLevelComment' => $this->comment('bad', 'Ty si úplný kokot.')]],
            ['snippet' => ['topLevelComment' => $this->comment('good', 'Ďakujem za pekné zamyslenie.')],
                'replies' => ['comments' => [$this->comment('bad-reply', 'Postrieľajte ich všetkých.')]]],
        ]);
        $this->assertSame(1, (new CommentSync)->handle()['comments']);
        $this->assertDatabaseMissing('comments', ['youtube_comment_id' => 'bad']);
        $this->assertDatabaseMissing('comments', ['youtube_comment_id' => 'bad-reply']);
        $this->assertDatabaseHas('comments', ['youtube_comment_id' => 'good']);
    }

    public function test_ulozi_vlakna_s_odpovedami_datumom_a_avatarom(): void
    {
        $post = $this->videoPost();
        $longAvatar = 'https://yt3.ggpht.com/' . str_repeat('a', 300);

        $this->fakeThreads([[
            'snippet' => ['topLevelComment' => $this->comment('top1', 'Krásna a povzbudivá kázeň, ďakujem.')],
            'replies' => ['comments' => [
                $this->comment('top1.r2', 'Súhlasím, veľmi mi to pomohlo.', ['publishedAt' => '2026-09-03T10:00:00Z']),
                $this->comment('top1.r1', 'Aj ja ďakujem za toto slovo.', [
                    'publishedAt' => '2026-09-02T10:00:00Z',
                    'authorProfileImageUrl' => $longAvatar,
                ]),
            ]],
        ]]);

        $stats = (new CommentSync)->handle();

        $this->assertSame(['posts' => 1, 'comments' => 3], $stats);

        $top = Comment::where('youtube_comment_id', 'top1')->firstOrFail();
        $this->assertNull($top->parent_id);
        $this->assertSame('Pisateltop1', $top->user_name);
        $this->assertSame('https://yt3.ggpht.com/avatar-top1', $top->user_avatar);
        $this->assertTrue($top->created_at->utc()->eq('2026-09-01 10:00:00'));
        $this->assertTrue($top->fromYoutube());

        $replies = Comment::where('parent_id', $top->id)->orderBy('id')->get();
        $this->assertSame(['top1.r1', 'top1.r2'], $replies->pluck('youtube_comment_id')->all());
        // Adresa dlhšia než stĺpec sa neukladá orezaná.
        $this->assertNull($replies->first()->user_avatar);

        $post->refresh();
        $this->assertNotNull($post->comments_synced_at);
        $this->assertSame('PT12M3S', $post->getRawOriginal('video_duration'));
    }

    public function test_opakovana_synchronizacia_neduplikuje(): void
    {
        $post = $this->videoPost();

        $this->fakeThreads([[
            'snippet' => ['topLevelComment' => $this->comment('top1', 'Krásna a povzbudivá kázeň, ďakujem.')],
        ]]);

        (new CommentSync)->handle();
        $post->forceFill(['comments_synced_at' => null])->save();
        (new CommentSync)->handle();

        $this->assertSame(1, Comment::count());
    }

    public function test_stary_komentar_sa_sparuje_podla_textu(): void
    {
        $post = $this->videoPost();
        $legacy = $post->comments()->create([
            'user_id' => CommentSync::USER_ID,
            'body' => 'Krásna a povzbudivá kázeň, ďakujem.',
            'user_name' => '@Pisatel',
            'user_avatar' => null,
        ]);

        $this->fakeThreads([[
            'snippet' => ['topLevelComment' => $this->comment('top1', 'Krásna a povzbudivá kázeň, ďakujem.')],
        ]]);

        (new CommentSync)->handle();

        $this->assertSame(1, Comment::count());
        $this->assertSame('top1', $legacy->refresh()->youtube_comment_id);
        $this->assertSame('https://yt3.ggpht.com/avatar-top1', $legacy->user_avatar);
    }

    public function test_kratky_komentar_a_odkaz_sa_preskocia(): void
    {
        $this->videoPost();

        $this->fakeThreads([
            ['snippet' => ['topLevelComment' => $this->comment('c1', 'Amen')]],
            ['snippet' => ['topLevelComment' => $this->comment('c2', 'Pozrite si https://spam.example')]],
        ]);

        (new CommentSync)->handle();

        $this->assertSame(0, Comment::count());
    }

    public function test_vypnute_komentare_nie_su_chyba(): void
    {
        $post = $this->videoPost();

        $this->fakeYoutube([
            'videos' => $this->videosFrom([
                self::VIDEO => $this->videoResource(self::VIDEO, ['statistics' => ['commentCount' => '3']]),
            ]),
            'commentThreads' => $this->youtubeError(403, 'commentsDisabled'),
        ]);

        $this->assertSame(['posts' => 1, 'comments' => 0], (new CommentSync)->handle());
        $this->assertNotNull($post->refresh()->comments_synced_at);
    }

    public function test_zmazane_video_sa_oznaci_ako_nedostupne(): void
    {
        $post = $this->videoPost();

        $this->fakeYoutube(['videos' => ['items' => []]]);

        (new CommentSync)->handle();

        $this->assertFalse((bool) $post->refresh()->video_available);
        $this->assertCount(0, $this->youtubeRequests('commentThreads'));
    }

    public function test_nove_video_sa_po_par_dnoch_synchronizuje_znova_stare_nie(): void
    {
        $nove = $this->videoPost(['comments_synced_at' => now()->subDays(4)]);
        $this->videoPost([
            'video_id' => 'video000002',
            'created_at' => now()->subDays(40),
            'comments_synced_at' => now()->subDays(4),
        ]);
        $this->videoPost(['video_id' => 'video000003', 'comments_synced_at' => now()->subDay()]);

        $this->assertSame([$nove->id], (new CommentSync)->candidates(50)->pluck('id')->all());
    }
}
