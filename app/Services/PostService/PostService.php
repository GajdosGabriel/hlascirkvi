<?php

namespace App\Services\PostService;

use App\Models\Canal;
use Illuminate\Support\Facades\DB;
use App\Services\Files\Form;

class PostService
{
    /** @var array<int, string> hlásenia o obrázkoch z posledného uloženia */
    public array $failures = [];

    public function store($canal, $request)
    {
        $data = $this->attributes($request, null);

        // validated() už zaručuje, že vybraný kanál smie používateľ použiť.
        if (! empty($data['canal_id']) && (int) $data['canal_id'] !== $canal->id) {
            $canal = Canal::findOrFail($data['canal_id']);
        }

        // validated() namiesto all() — do modelu sa tak nedostane nič, čo
        // PostSaveRequest nepovolil (count_view, cudzie canal_id).
        $post = DB::transaction(function () use ($canal, $data, $request) {
            $post = $canal->posts()->create($data);
            $this->syncCollections($post, $request);
            return $post;
        });

        // Obrázky a sťahovanie náhľadu až po commite — pomalé YouTube by inak
        // držalo zámky a pri rollbacku by na disku ostali siroty.
        $this->failures = (new Form($post, $request))->handler();

        return $post;
    }

    public function update($post, $request)
    {
        DB::transaction(function () use ($post, $request) {
            $post->update($this->attributes($request, $post));
            $this->syncCollections($post, $request);
        });

        $this->failures = (new Form($post, $request, $post->wasChanged('video_id')))->handler();

        return $post;
    }

    /**
     * Polia na zápis. Formulár posiela zaradenie (`section`) a prepínač „ísť
     * von teraz"; z toho druhého sa skladá `published_at`.
     *
     * Do 9/2026 tu bolo `$post->updaters()->sync($request->get('updaters'))` —
     * jeden riadok v spojovacej tabuľke, ktorý znamenal zaradenie aj stav
     * zverejnenia naraz. Prepínač „Teraz / Neskôr" pritom písal do poľa
     * `published`, ktoré vo validácii nebolo, takže sa zahodilo a voľba
     * nerobila nič.
     *
     * @param  \App\Models\Post|null  $post
     * @return array<string, mixed>
     */
    protected function attributes($request, $post): array
    {
        $data = collect($request->validated())->except(['publish_now', 'collections', 'collections_present'])->all();

        // posts.body je NOT NULL; príspevok s videom smie byť bez textu.
        if (array_key_exists('body', $data)) {
            $data['body'] ??= '';
        }

        if ($request->has('publish_now')) {
            $data['published_at'] = $request->boolean('publish_now')
                // Raz zverejnený príspevok si čas vydania ponechá — inak by
                // sa každou úpravou posunul dopredu a vyskočil na začiatok
                // výpisu.
                ? ($post?->published_at ?: now())
                : null;
        }

        return $data;
    }

    protected function syncCollections($post, $request): void
    {
        if ($request->boolean('collections_present') || $request->has('collections')) {
            $ids = $request->validated('collections', []);
            if (! $post->wasChanged('canal_id')) {
                // Historické semináre mohli zoskupovať videá z iných kanálov.
                // Formulár ich neponúka; odstrániť ich môže správca kolekcie.
                $ids = array_merge($ids, $post->seminars()->where('seminars.canal_id', '!=', $post->canal_id)->pluck('seminars.id')->all());
            }
            $post->seminars()->sync($ids);
        } elseif ($post->wasChanged('canal_id')) {
            // Pri presune sa pôvodné zaradenia nemajú preniesť do cudzieho kanála.
            $post->seminars()->detach();
        }
    }
}
