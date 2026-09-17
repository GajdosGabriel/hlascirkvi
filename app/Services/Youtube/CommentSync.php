<?php

namespace App\Services\Youtube;

use App\Enums\PostSection;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Eloquent\EloquentPostRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Komentáre z YouTube k videám na webe.
 *
 * Predtým sa každú hodinu spracovalo jediné video, duplicita sa hľadala
 * podľa textu, odpovede a dátum sa zahodili a adresa avatara sa orezala na
 * sto znakov. Teraz ide dávka videí jedným videos.list a ku každému video
 * s komentármi jedno commentThreads (vlákna aj pribalené odpovede).
 *
 * Kvóta: 1 + počet videí s komentármi, najviac 51 jednotiek za beh.
 */
class CommentSync
{
    /** Anonymný používateľ, pod ktorým stoja komentáre z YouTube. */
    public const USER_ID = 100;

    public const BATCH = 50;

    /** Komentáre k novým videám ešte pribúdajú — tie sa prejdú znova. */
    private const RECENT_DAYS = 30;

    private const RESYNC_AFTER_DAYS = 3;

    private const MIN_LENGTH = 10;

    public function __construct(private ?YoutubeApi $api = null)
    {
        $this->api ??= app(YoutubeApi::class);
    }

    /**
     * @return array{posts: int, comments: int}
     */
    public function handle(int $limit = self::BATCH): array
    {
        $stats = ['posts' => 0, 'comments' => 0];
        $posts = $this->candidates(max(1, min($limit, YoutubeApi::BATCH)));

        if ($posts->isEmpty()) {
            return $stats;
        }

        try {
            $videos = $this->api->videos($posts->pluck('video_id')->all(), 'contentDetails,status,statistics');
        } catch (YoutubeApiException $e) {
            Log::warning('Synchronizácia komentárov z YouTube zlyhala: ' . $e->getMessage());

            return $stats;
        }

        foreach ($posts as $post) {
            try {
                $stats['comments'] += $this->syncPost($post, $videos[$post->video_id] ?? null);
                $stats['posts']++;
            } catch (\Throwable $e) {
                // Video bez označenej synchronizácie sa skúsi v ďalšom behu.
                Log::warning('Sťahovanie komentárov z YouTube zlyhalo: ' . $e->getMessage(), [
                    'post_id' => $post->id,
                    'video_id' => $post->video_id,
                ]);

                if ($e instanceof YoutubeApiException && $e->stopsRun()) {
                    break;
                }
            }
        }

        return $stats;
    }

    /**
     * Najprv videá, ktoré ešte synchronizované neboli (najnovšie prvé), potom
     * nové videá so staršou synchronizáciou.
     */
    public function candidates(int $limit): Collection
    {
        $query = fn () => (new EloquentPostRepository)->postsInSection(PostSection::Front)
            ->without(['favorites', 'images', 'canal'])
            ->whereNotNull('video_id');

        $posts = $query()->whereNull('comments_synced_at')->latest('id')->limit($limit)->get();

        if ($posts->count() < $limit) {
            $posts = $posts->concat(
                $query()
                    ->where('created_at', '>=', now()->subDays(self::RECENT_DAYS))
                    ->where('comments_synced_at', '<=', now()->subDays(self::RESYNC_AFTER_DAYS))
                    ->oldest('comments_synced_at')
                    ->limit($limit - $posts->count())
                    ->get()
            );
        }

        return $posts;
    }

    protected function syncPost(Post $post, ?object $video): int
    {
        // Zmazané a súkromné video YouTube vo videos.list nevráti.
        if ($video === null) {
            $post->update(['video_available' => false, 'comments_synced_at' => now()]);

            return 0;
        }

        $updates = ['comments_synced_at' => now()];
        $duration = $video->contentDetails->duration ?? null;

        if ($post->getRawOriginal('video_duration') === null && $duration && $duration !== 'P0D') {
            $updates['video_duration'] = $duration;
        }

        if (! ($video->status->embeddable ?? false)) {
            $post->update($updates + ['video_available' => false]);

            return 0;
        }

        $saved = (int) ($video->statistics->commentCount ?? 0) > 0
            ? $this->syncThreads($post)
            : 0;

        $post->update($updates);

        return $saved;
    }

    protected function syncThreads(Post $post): int
    {
        try {
            $threads = $this->api->commentThreads($post->video_id)->items;
        } catch (YoutubeApiException $e) {
            // Vypnuté komentáre nie sú chyba, len nie je čo sťahovať.
            if ($e->is('commentsDisabled', 'videoNotFound')) {
                return 0;
            }

            throw $e;
        }

        if ($threads === []) {
            return 0;
        }

        $ids = collect($threads)
            ->flatMap(fn ($thread) => array_merge(
                [$thread->snippet->topLevelComment->id ?? null],
                array_map(fn ($reply) => $reply->id ?? null, $thread->replies->comments ?? [])
            ))
            ->filter()
            ->values()
            ->all();

        $known = Comment::withTrashed()->without('favorites')
            ->whereIn('youtube_comment_id', $ids)
            ->pluck('id', 'youtube_comment_id')
            ->all();

        // Komentáre zo starého sťahovania nemajú ID — spárujú sa podľa textu
        // a doplní sa im ID aj neorezaný avatar.
        $legacy = $post->comments()->withTrashed()->without(['user', 'favorites'])
            ->where('user_id', self::USER_ID)
            ->whereNull('youtube_comment_id')
            ->get()
            ->keyBy(fn (Comment $comment) => $this->normalize($comment->body));

        $saved = 0;

        foreach ($threads as $thread) {
            $top = $thread->snippet->topLevelComment ?? null;

            if ($top === null) {
                continue;
            }

            $parentId = $this->store($post, $top, null, $known, $legacy, $saved);

            // Odpoveď bez uloženého hlavného komentára nemá kam patriť.
            if ($parentId === null) {
                continue;
            }

            $replies = $thread->replies->comments ?? [];
            usort($replies, fn ($a, $b) => strcmp($a->snippet->publishedAt ?? '', $b->snippet->publishedAt ?? ''));

            foreach ($replies as $reply) {
                $this->store($post, $reply, $parentId, $known, $legacy, $saved);
            }
        }

        return $saved;
    }

    /**
     * @return int|null ID komentára na webe — nového, známeho alebo spárovaného
     */
    protected function store(Post $post, object $comment, ?int $parentId, array &$known, Collection $legacy, int &$saved): ?int
    {
        $youtubeId = $comment->id ?? null;

        if ($youtubeId !== null && isset($known[$youtubeId])) {
            return $known[$youtubeId];
        }

        $snippet = $comment->snippet;
        $body = trim(cleanHardSpace((string) ($snippet->textOriginal ?? $snippet->textDisplay ?? '')));

        $author = [
            'user_name' => Str::limit(ltrim((string) ($snippet->authorDisplayName ?? ''), '@'), 100, '') ?: null,
            'user_avatar' => $this->avatar($snippet->authorProfileImageUrl ?? null),
        ];

        if ($match = $legacy->get($this->normalize($body))) {
            $match->forceFill($author + ['youtube_comment_id' => $youtubeId])->save();

            return $known[$youtubeId] = $match->id;
        }

        if (mb_strlen($body) < self::MIN_LENGTH || preg_match('/(http|www\.|mailto)/i', $body)) {
            return null;
        }

        $model = $post->comments()->create($author + [
            'user_id' => self::USER_ID,
            'parent_id' => $parentId,
            'body' => $body,
            'youtube_comment_id' => $youtubeId,
            'created_at' => isset($snippet->publishedAt)
                ? Carbon::parse($snippet->publishedAt)->setTimezone(config('app.timezone'))
                : now(),
        ]);

        $saved++;

        if ($youtubeId !== null) {
            $known[$youtubeId] = $model->id;
        }

        return $model->id;
    }

    /** Adresa, ktorá sa do stĺpca nezmestí, by sa orezala a nenačítala. */
    protected function avatar(?string $url): ?string
    {
        return is_string($url) && str_starts_with($url, 'https://') && strlen($url) <= 255 ? $url : null;
    }

    protected function normalize(string $body): string
    {
        $text = html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $body)), ENT_QUOTES | ENT_HTML5);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
    }
}
