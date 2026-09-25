<?php

namespace App\Services;

use App\Models\AiUsage;
use App\Models\Comment;
use App\Models\Post;
use App\Notifications\Comments\GuestRepliedToComment;
use App\Support\OpenAiChat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Hosť (autor komentára stiahnutého z YouTube) na webe odpovedať nemôže.
 * Keď mu niekto odpovie (príznak `reply_to_guest`), po DELAY_HOURS za neho
 * zareaguje portál krátkou odpoveďou od AI — podľa charakteru odpovede,
 * povzbudivo a mierne.
 *
 * Odpoveď je pod menom portálu, nie hosťa: nevkladáme reálnemu človeku do úst
 * slová, ktoré nenapísal. Cena sa počíta do mesačného limitu AI (/admin/ai).
 */
class GuestReplier
{
    public const FEATURE = 'guest_reply';

    public const DELAY_HOURS = 3;

    /** Meno, pod ktorým sa odpoveď zobrazí. */
    public const AUTHOR_NAME = 'Hlas Cirkvi';

    public function __construct(protected PostSummarizer $summarizer)
    {
    }

    public function isConfigured(): bool
    {
        return $this->summarizer->isConfigured();
    }

    public function budgetExhausted(): bool
    {
        return $this->summarizer->budgetExhausted();
    }

    /**
     * Vlákna (ID hlavného komentára hosťa), v ktorých najstaršia čakajúca
     * odpoveď je staršia než DELAY_HOURS.
     *
     * @return Collection<int, int>
     */
    public function dueThreads(int $limit = 20): Collection
    {
        return Comment::query()
            ->without('favorites')
            ->where('reply_to_guest', true)
            ->whereNotNull('parent_id')
            ->groupBy('parent_id')
            ->havingRaw('min(created_at) <= ?', [now()->subHours(self::DELAY_HOURS)])
            ->orderByRaw('min(created_at)')
            ->limit($limit)
            ->pluck('parent_id');
    }

    /**
     * Zareaguje na všetky čakajúce odpovede vo vlákne jedným komentárom.
     * Vráti ho, alebo null, keď nebolo na čo alebo čím odpovedať. Pri chybe
     * API príznak ostáva a ďalší beh to skúsi znova.
     */
    public function replyToThread(int $parentId): ?Comment
    {
        $parent = Comment::without('favorites')->find($parentId);
        $pending = Comment::without('favorites')->with('user')
            ->where('parent_id', $parentId)
            ->where('reply_to_guest', true)
            ->oldest()
            ->get();

        $post = $parent?->commentable;

        // Hlavný komentár alebo príspevok medzitým zmizol — nie je kam odpovedať.
        if (! $parent || ! $parent->published || ! $post instanceof Post) {
            $this->clear($pending);

            return null;
        }

        $thread = Comment::without('favorites')->with('user')
            ->where('parent_id', $parentId)
            ->published()
            ->oldest()
            ->get();

        try {
            $body = $this->generate($post, $parent, $thread, $pending);
        } catch (Throwable $e) {
            Log::warning('GuestReplier: odpoveď za hosťa zlyhala', [
                'comment_id' => $parentId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($body === null) {
            $this->clear($pending);

            return null;
        }

        $reply = $post->comments()->create([
            'user_id' => \App\Services\Youtube\CommentSync::USER_ID,
            'user_name' => self::AUTHOR_NAME,
            'parent_id' => $parentId,
            'body' => $body,
        ]);

        $this->clear($pending);

        // Tomu, kto hosťovi odpovedal, príde zvonček aj e-mail — s jeho
        // poslednou odpoveďou vo vlákne.
        $pending->filter(fn (Comment $c) => $c->user && $c->user->id !== \App\Services\Youtube\CommentSync::USER_ID)
            ->groupBy('user_id')
            ->each(fn (Collection $own) => $own->last()->user->notify(new GuestRepliedToComment($reply, $own->last())));

        return $reply;
    }

    protected function clear(Collection $pending): void
    {
        Comment::whereKey($pending->modelKeys())->update(['reply_to_guest' => false]);
    }

    protected function generate(Post $post, Comment $parent, Collection $thread, Collection $pending): ?string
    {
        $name = fn (Comment $c) => $c->user_name ?: trim(($c->user?->first_name ?? '') . ' ' . ($c->user?->last_name ?? '')) ?: 'Niekto';
        $quote = fn (Comment $c) => '- ' . $name($c) . ': „' . mb_substr(trim($c->body), 0, 1500) . '“';

        $conversation = "Príspevok: {$post->title}\n\n"
            . "Komentár hosťa z YouTube:\n" . $quote($parent) . "\n\n"
            . "Odpovede na webe (od najstaršej):\n"
            . $thread->map($quote)->implode("\n") . "\n\n"
            . "Zareaguj na tieto odpovede:\n"
            . $pending->map($quote)->implode("\n");

        $model = (string) config('openai.reply_model');

        $response = OpenAiChat::create(OpenAiChat::params($model, 250, 0.6) + [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Si správca diskusie kresťanského portálu Hlas Cirkvi. Pôvodný komentár napísal človek '
                        . 'na YouTube, ktorý na webe odpovedať nemôže, preto na odpovede, ktoré mu tu ľudia napísali, '
                        . 'reaguješ za portál. Nevydávaj sa za autora pôvodného komentára ani za iného konkrétneho človeka '
                        . 'a nehovor za neho, čo si myslí. '
                        . 'Napíš po slovensky krátku odpoveď (1 až 3 vety) podľa charakteru odpovede: poďakovanie prijmi '
                        . 's vďakou, svedectvo či modlitbu s úctou podpor, na otázku odpovedz len tým, čo vyplýva z textu, '
                        . 'inak povzbuď k hľadaniu odpovede; nesúhlas, kritiku alebo hnev prijmi pokojne a zmierlivo, '
                        . 'bez hádky a moralizovania. Tón je vždy povzbudivý, mierny a láskavý. '
                        . 'Oslov človeka menom, ak je uvedené. Nevymýšľaj fakty, nepridávaj odkazy ani výzvy na odber. '
                        . 'Ak ide o spam alebo odpoveď nemá zmysel, napíš iba znak -.',
                ],
                ['role' => 'user', 'content' => $conversation],
            ],
        ]);

        if ($response->usage) {
            AiUsage::record(
                self::FEATURE,
                $post->id,
                $response->model ?: $model,
                $response->usage->promptTokens,
                (int) $response->usage->completionTokens,
            );
        }

        $body = trim((string) ($response->choices[0]->message->content ?? ''), " \n\r\t\"„“");

        return mb_strlen($body) < 3 ? null : $body;
    }
}
