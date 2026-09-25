<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Services\PostSummarizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * AI zhrnutia: vypínač, limit a spotreba. Zostatok kreditu OpenAI cez API
 * s bežným kľúčom zistiť nejde, preto stránka ukazuje vlastnú evidenciu
 * volaní (ai_usages) a odkaz na vyúčtovanie OpenAI.
 */
class AiController extends Controller
{
    public function index(PostSummarizer $summarizer)
    {
        $month = AiUsage::thisMonth()->toBase()
            ->selectRaw('COUNT(*) as calls, COALESCE(SUM(prompt_tokens), 0) as prompt, COALESCE(SUM(completion_tokens), 0) as completion, COALESCE(SUM(cost_usd), 0) as cost')
            ->first();

        $total = AiUsage::query()->toBase()
            ->selectRaw('COUNT(*) as calls, COALESCE(SUM(prompt_tokens + completion_tokens), 0) as tokens, COALESCE(SUM(cost_usd), 0) as cost')
            ->first();

        // Po dňoch za 30 dní — aby bolo vidieť, koľko stojí bežný deň.
        $daily = AiUsage::query()->toBase()
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as calls, SUM(prompt_tokens + completion_tokens) as tokens, SUM(cost_usd) as cost')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('day')
            ->get();

        $averageCost = $total->calls > 0 ? $total->cost / $total->calls : null;

        // Hrubý odhad fronty: zverejnené príspevky bez pokusu o zhrnutie,
        // ktoré majú video (titulky) alebo popis dlhší než ~700 znakov
        // (zhruba MIN_WORDS slov). Či video titulky naozaj má, sa zistí až pri volaní.
        $waiting = Post::query()
            ->published()
            ->whereNull('summary_generated_at')
            ->where(fn ($q) => $q->whereNotNull('video_id')->orWhereRaw('CHAR_LENGTH(body) >= 700'))
            ->count();

        return view('admins.ai', [
            'configured'  => $summarizer->isConfigured(),
            'enabled'     => $summarizer->enabled(),
            'batch'       => $summarizer->batchSize(),
            'limit'       => $summarizer->monthlyLimit(),
            'length'      => $summarizer->length(),
            'lengths'     => PostSummarizer::LENGTHS,
            'model'       => config('openai.summary_model'),
            'month'       => $month,
            'total'       => $total,
            'daily'       => $daily,
            'averageCost' => $averageCost,
            'waiting'     => $waiting,
            'summarized'  => Post::query()->whereNotNull('summary')->count(),
            'recent'      => AiUsage::with(['post' => fn ($q) => $q->without(['favorites', 'images', 'canal'])->select('id', 'title', 'slug')])
                ->latest('id')->limit(15)->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'batch'   => ['required', 'integer', 'min:1', 'max:200'],
            'limit'   => ['required', 'numeric', 'min:0', 'max:1000'],
            'length'  => ['required', Rule::in(array_keys(PostSummarizer::LENGTHS))],
        ]);

        Setting::set(PostSummarizer::SETTING_ENABLED, $request->boolean('enabled') ? 1 : 0);
        Setting::set(PostSummarizer::SETTING_BATCH, (int) $data['batch']);
        Setting::set(PostSummarizer::SETTING_LIMIT, (float) $data['limit']);
        Setting::set(PostSummarizer::SETTING_LENGTH, $data['length']);

        return back()->with('flash', 'Nastavenie AI zhrnutí je uložené.');
    }

    /**
     * Vynútené zhrnutie jedného príspevku — hneď, aj pri vypnutých dávkach,
     * aj keď už zhrnutie má a aj pri popise kratšom než MIN_WORDS. Mesačný
     * limit platí aj tu. Rozsah sa dá zvoliť len pre toto volanie (na skúšanie),
     * inak platí uložené nastavenie.
     */
    public function summarize(Request $request, PostSummarizer $summarizer)
    {
        $data = $request->validate([
            'post'   => ['required', 'string', 'max:500'],
            'length' => ['nullable', Rule::in(array_keys(PostSummarizer::LENGTHS))],
        ]);

        // Stačí ID alebo celá adresa príspevku (/post/123/slug).
        $id = preg_match('#/post/(\d+)#', $data['post'], $m) ? (int) $m[1] : (int) $data['post'];
        $post = Post::query()->find($id);

        $message = match (true) {
            $post === null                   => 'Príspevok sa nenašiel.',
            ! $summarizer->isConfigured()    => 'Chýba OPENAI_API_KEY v .env.',
            $summarizer->budgetExhausted()   => 'Mesačný limit je vyčerpaný. Zvýšte ho alebo počkajte na ďalší mesiac.',
            $summarizer->wordCount($post) === 0 => 'Príspevok nemá popis ani titulky videa, nie je čo zhrnúť.',
            default                          => null,
        };

        $length = $data['length'] ?? $summarizer->length();
        $summary = $message === null ? $summarizer->summarizeAndStore($post, $length, true) : null;

        if ($message === null) {
            $message = $summary !== null
                ? 'Zhrnutie príspevku „' . $post->title . '“ je hotové.'
                : 'Zhrnutie sa nepodarilo vytvoriť (podrobnosti v logu).';
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => $summary !== null, 'message' => $message, 'summary' => $summary]);
        }

        // Výsledok sa ukáže priamo na stránke, aby sa dalo porovnávať rozsahy
        // bez prekliku na príspevok.
        $result = $summary === null ? null : [
            'post_id' => $post->id,
            'title'   => $post->title,
            'url'     => route('post.show', [$post->id, $post->slug]),
            'length'  => $length,
            'words'   => $summarizer->wordCount($post),
            'source'  => $summarizer->lastSource,
            'summary' => $summary,
            'tokens'  => $summarizer->lastUsage ? $summarizer->lastUsage->prompt_tokens + $summarizer->lastUsage->completion_tokens : null,
            'cost'    => $summarizer->lastUsage?->cost_usd,
        ];

        return back()->withInput()->with('flash', $message)->with('ai_result', $result);
    }
}
