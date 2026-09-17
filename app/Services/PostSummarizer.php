<?php

namespace App\Services;

use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Support\Seo;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

/**
 * Krátke zhrnutie popisu príspevku („V skratke" na detaile).
 *
 * Zhrnutie vychádza len z textu, ktorý je na stránke — model dostane popis,
 * nie video. Krátke popisy sa nezhŕňajú: zhrnutie by bolo dlhšie než text.
 *
 * Automatické dávky sa riadia administráciou (/admin/ai): vypínač, veľkosť
 * dávky a mesačný limit v USD. Každé volanie sa zapíše do `ai_usages`.
 */
class PostSummarizer
{
    public const FEATURE = 'post_summary';

    /** Pod túto dĺžku popisu zhrnutie nemá zmysel. */
    public const MIN_WORDS = 120;

    /** Model dostane najviac toľko znakov; dlhé prepisy by len míňali tokeny. */
    protected const MAX_CHARS = 12000;

    public const SETTING_ENABLED = 'ai_summary.enabled';
    public const SETTING_BATCH = 'ai_summary.batch';
    public const SETTING_LIMIT = 'ai_summary.monthly_limit_usd';

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
        $text = Seo::text($post->body);

        return $text !== '' && count(preg_split('/\s+/u', $text)) >= self::MIN_WORDS;
    }

    /**
     * Vytvorí zhrnutie a uloží ho k príspevku. Aj prázdny výsledok dostane
     * čas, inak by dávka krátke popisy skúšala stále dookola. forceFill +
     * saveQuietly: updated_at ani udalosti modelu sa nemenia, zhrnutie nie je
     * úprava príspevku.
     */
    public function summarizeAndStore(Post $post): ?string
    {
        $summary = $this->summarize($post);

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
     */
    public function summarize(Post $post): ?string
    {
        if (! $this->worthSummarizing($post)) {
            return null;
        }

        $text = mb_substr(Seo::text($post->body), 0, self::MAX_CHARS);
        $model = (string) config('openai.summary_model');

        try {
            $response = OpenAI::chat()->create([
                'model' => $model,
                'temperature' => 0.3,
                'max_tokens' => 400,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Si redaktor kresťanského portálu Hlas Cirkvi. Z popisu kázne, prednášky alebo článku '
                            . 'napíš po slovensky zhrnutie hlavných myšlienok: 3 až 5 krátkych bodov, každý na samostatnom '
                            . 'riadku začínajúcom znakom „• ". Používaj len to, čo je v texte — nič nedopĺňaj ani nehodnoť. '
                            . 'Vynechaj odkazy, kontakty, výzvy na odber a čísla účtov.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Názov: {$post->title}\n\nText:\n{$text}",
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
            AiUsage::record(
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
