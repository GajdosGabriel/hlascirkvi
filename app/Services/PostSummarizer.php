<?php

namespace App\Services;

use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Support\OpenAiChat;
use App\Support\Seo;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Zhrnutie príspevku („V skratke" / „Z obsahu" na detaile).
 *
 * Podklad je prepis reči z titulkov videa (YoutubeCaptions), a keď titulky
 * nie sú alebo sú kratšie, popis príspevku. Krátke texty sa v dávke
 * nezhŕňajú: zhrnutie by bolo dlhšie než text.
 *
 * Automatické dávky sa riadia administráciou (/admin/ai): vypínač, veľkosť
 * dávky, rozsah zhrnutia a mesačný limit v USD. Každé volanie sa zapíše do
 * `ai_usages`.
 */
class PostSummarizer
{
    public const FEATURE = 'post_summary';

    /** Pod túto dĺžku popisu zhrnutie nemá zmysel. */
    public const MIN_WORDS = 120;

    /**
     * Model dostane najviac toľko znakov. Prepis hodinovej kázne má ~50 000
     * znakov (~15 000 tokenov, s gpt-6-luna ~$0,0015); dlhšie sa oreže.
     */
    protected const MAX_CHARS = 80000;

    public const SETTING_ENABLED = 'ai_summary.enabled';
    public const SETTING_BATCH = 'ai_summary.batch';
    public const SETTING_LIMIT = 'ai_summary.monthly_limit_usd';
    public const SETTING_LENGTH = 'ai_summary.length';

    /**
     * Rozsah zhrnutia: pokyn pre model a strop výstupných tokenov (s rezervou —
     * slovenčina je na tokeny drahšia než angličtina, A4 ≈ 500 slov ≈ 1 500 tokenov).
     */
    public const LENGTHS = [
        'short'    => ['label' => 'Krátke (2–3 body)',     'max_tokens' => 250,  'instruction' => self::BULLETS . '2 až 3 body, každý bod jedna krátka veta.'],
        'medium'   => ['label' => 'Stredné (3–5 bodov)',   'max_tokens' => 400,  'instruction' => self::BULLETS . '3 až 5 bodov, každý bod jedna krátka veta.'],
        'long'     => ['label' => 'Dlhšie (5–7 bodov)',    'max_tokens' => 700,  'instruction' => self::BULLETS . '5 až 7 bodov, každý bod jedna až dve vety.'],
        'detailed' => ['label' => 'Podrobné (7–10 bodov)', 'max_tokens' => 1200, 'instruction' => self::BULLETS . '7 až 10 bodov, každý bod dve až tri úplné vety.'],
        'a4'       => ['label' => 'Text na A4 (~500 slov)', 'max_tokens' => 2000, 'instruction' =>
            'Napíš po slovensky súvislý text na jednu stranu A4 (400 až 550 slov), nie body. '
            . 'Začni jedným-dvoma vetami, o čom text je. Potom vyber najzaujímavejšiu alebo najsilnejšiu myšlienku '
            . 'či pasáž a rozviň ju podrobnejšie — ak je v texte výstižná veta, môžeš ju doslovne citovať v úvodzovkách „…“. '
            . 'Odseky oddeľ prázdnym riadkom, bez nadpisov a bez formátovania Markdown. '
            . 'Ak je text krátky, napíš radšej menej, než by si mal dopĺňať.'],
    ];

    /** Spoločný začiatok pokynu pre zhrnutie v bodoch. */
    protected const BULLETS = 'Napíš po slovensky zhrnutie hlavných myšlienok, každý bod na samostatnom riadku začínajúcom '
        . 'znakom „• ". Ak text na toľko bodov nestačí, napíš menej. Rozsah: ';

    public const DEFAULT_LENGTH = 'medium';

    /** Záznam posledného volania — administrácia ukáže jeho tokeny a cenu. */
    public ?AiUsage $lastUsage = null;

    /** Z čoho bolo posledné zhrnutie: 'captions' alebo 'body'. */
    public ?string $lastSource = null;

    public function __construct(protected YoutubeCaptions $captions)
    {
    }

    public function isConfigured(): bool
    {
        return (string) config('openai.api_key') !== '';
    }

    /** Automatické dávky sú predvolene vypnuté — kým ich správca nezapne. */
    public function enabled(): bool
    {
        return (bool) Setting::get(self::SETTING_ENABLED, false);
    }

    public function batchSize(): int
    {
        return max(1, (int) Setting::get(self::SETTING_BATCH, 30));
    }

    /** Mesačný strop v USD; 0 znamená bez limitu. */
    public function monthlyLimit(): float
    {
        return max(0.0, (float) Setting::get(self::SETTING_LIMIT, 1));
    }

    /** Uložený rozsah zhrnutia (kľúč z LENGTHS). */
    public function length(): string
    {
        $length = (string) Setting::get(self::SETTING_LENGTH, self::DEFAULT_LENGTH);

        return isset(self::LENGTHS[$length]) ? $length : self::DEFAULT_LENGTH;
    }

    public function monthCost(): float
    {
        return (float) AiUsage::thisMonth()->sum('cost_usd');
    }

    public function budgetExhausted(): bool
    {
        $limit = $this->monthlyLimit();

        return $limit > 0 && $this->monthCost() >= $limit;
    }

    public function worthSummarizing(Post $post): bool
    {
        return $this->wordCount($post) >= self::MIN_WORDS;
    }

    public function wordCount(Post $post): int
    {
        return $this->words($this->source($post)['text']);
    }

    /**
     * Z čoho sa zhŕňa: prepis reči z titulkov videa, ak je dlhší než popis
     * (pri kázňach býva popis len meno a dátum), inak popis príspevku.
     *
     * @return array{text: string, from: 'captions'|'body'}
     */
    public function source(Post $post): array
    {
        $body = Seo::text($post->body);
        $captions = $this->captions->text($post->video_id);

        return $captions !== null && $this->words($captions) > $this->words($body)
            ? ['text' => $captions, 'from' => 'captions']
            : ['text' => $body, 'from' => 'body'];
    }

    protected function words(string $text): int
    {
        return $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    }

    /**
     * Vytvorí zhrnutie a uloží ho k príspevku. Aj prázdny výsledok dostane
     * čas, inak by dávka krátke popisy skúšala stále dookola. forceFill +
     * saveQuietly: updated_at ani udalosti modelu sa nemenia, zhrnutie nie je
     * úprava príspevku.
     */
    public function summarizeAndStore(Post $post, ?string $length = null, bool $force = false): ?string
    {
        $summary = $this->summarize($post, $length, $force);

        $post->forceFill([
            'summary' => $summary,
            'summary_generated_at' => now(),
        ])->saveQuietly();

        return $summary;
    }

    /**
     * Vráti zhrnutie, alebo null, keď text na zhrnutie nie je alebo volanie
     * zlyhalo. Chyba sa len zaloguje — príkaz beží v dávke a jeden príspevok
     * nesmie zastaviť ostatné.
     *
     * $length je kľúč z LENGTHS (null = uložené nastavenie). $force preskočí
     * minimálnu dĺžku popisu — pre ručné vynútenie z administrácie.
     */
    public function summarize(Post $post, ?string $length = null, bool $force = false): ?string
    {
        $this->lastUsage = null;
        $source = $this->source($post);
        $this->lastSource = $source['from'];
        $words = $this->words($source['text']);

        if ($force ? $words === 0 : $words < self::MIN_WORDS) {
            return null;
        }

        $text = mb_substr($source['text'], 0, self::MAX_CHARS);
        $model = (string) config('openai.summary_model');
        $size = self::LENGTHS[$length] ?? self::LENGTHS[$this->length()];

        $material = $source['from'] === 'captions'
            ? 'Dostaneš prepis reči z videa (kázeň, prednáška, rozhovor) z automatických titulkov — môže mať chyby '
                . 'v slovách a chýba interpunkcia; zmysel pochop z kontextu a zle rozpoznané slová neopakuj. '
            : 'Dostaneš popis kázne, prednášky alebo článku. ';
        $input = $source['from'] === 'captions'
            ? "Názov: {$post->title}\n\nPopis:\n" . mb_substr(Seo::text($post->body), 0, 2000) . "\n\nPrepis reči:\n{$text}"
            : "Názov: {$post->title}\n\nText:\n{$text}";

        try {
            $response = OpenAiChat::create(OpenAiChat::params($model, $size['max_tokens'], 0.3) + [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Si redaktor kresťanského portálu Hlas Cirkvi. ' . $material
                            . $size['instruction'] . ' '
                            . 'Používaj len to, čo je v texte — nič nedopĺňaj ani nehodnoť. '
                            . 'Vynechaj odkazy, kontakty, výzvy na odber a čísla účtov.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $input,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('PostSummarizer: zhrnutie príspevku zlyhalo', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->usage) {
            $this->lastUsage = AiUsage::record(
                self::FEATURE,
                $post->id,
                $response->model ?: $model,
                $response->usage->promptTokens,
                (int) $response->usage->completionTokens,
            );
        }

        $summary = trim((string) ($response->choices[0]->message->content ?? ''));

        return $summary === '' ? null : $summary;
    }
}
