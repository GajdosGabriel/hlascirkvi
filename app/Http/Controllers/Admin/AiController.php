<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsage;
use App\Models\Post;
use App\Models\Setting;
use App\Services\PostSummarizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Hrubý odhad fronty: zverejnené príspevky bez pokusu o zhrnutie
        // a s popisom dlhším než ~700 znakov (zhruba MIN_WORDS slov).
        $waiting = Post::query()
            ->published()
            ->whereNull('summary_generated_at')
            ->whereRaw('CHAR_LENGTH(body) >= 700')
            ->count();

        return view('admins.ai', [
            'configured'  => $summarizer->isConfigured(),
            'enabled'     => $summarizer->enabled(),
            'batch'       => $summarizer->batchSize(),
            'limit'       => $summarizer->monthlyLimit(),
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
        ]);

        Setting::set(PostSummarizer::SETTING_ENABLED, $request->boolean('enabled') ? 1 : 0);
        Setting::set(PostSummarizer::SETTING_BATCH, (int) $data['batch']);
        Setting::set(PostSummarizer::SETTING_LIMIT, (float) $data['limit']);

        return back()->with('flash', 'Nastavenie AI zhrnutí je uložené.');
    }

    /**
     * Vynútené zhrnutie jedného príspevku — aj pri vypnutých dávkach a aj
     * keď už zhrnutie má. Mesačný limit platí aj tu.
     */
    public function summarize(Request $request, PostSummarizer $summarizer)
    {
        $data = $request->validate(['post' => ['required', 'string', 'max:500']]);

        // Stačí ID alebo celá adresa príspevku (/post/123/slug).
        $id = preg_match('#/post/(\d+)#', $data['post'], $m) ? (int) $m[1] : (int) $data['post'];
        $post = Post::query()->find($id);

        $message = match (true) {
            $post === null                   => 'Príspevok sa nenašiel.',
            ! $summarizer->isConfigured()    => 'Chýba OPENAI_API_KEY v .env.',
            $summarizer->budgetExhausted()   => 'Mesačný limit je vyčerpaný. Zvýšte ho alebo počkajte na ďalší mesiac.',
            ! $summarizer->worthSummarizing($post) => 'Popis príspevku je príliš krátky na zhrnutie (menej ako ' . PostSummarizer::MIN_WORDS . ' slov).',
            default                          => null,
        };

        $summary = $message === null ? $summarizer->summarizeAndStore($post) : null;

        if ($message === null) {
            $message = $summary !== null
                ? 'Zhrnutie príspevku „' . $post->title . '“ je hotové.'
                : 'Zhrnutie sa nepodarilo vytvoriť (podrobnosti v logu).';
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => $summary !== null, 'message' => $message, 'summary' => $summary]);
        }

        return back()->with('flash', $message);
    }
}
