<?php

namespace App\Services\PostService;

use Illuminate\Support\Facades\DB;
use App\Services\Files\Form;

class PostService
{
    public function store($organization, $request)
    {
        DB::transaction(function () use ($organization, $request) {
            // validated() namiesto all() — do modelu sa tak nedostane nič, čo
            // PostSaveRequest nepovolil (count_view, cudzie organization_id).
            $post = $organization->posts()->create(
                $this->attributes($request, null)
            );

            $file = (new Form($post, $request))->handler();
            // $file->store();
        });
    }

    public function update($post, $request)
    {
        $post->update($this->attributes($request, $post));

        $file =  (new Form($post, $request))->handler();
        // $file->store();

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
        $data = collect($request->validated())->except('publish_now')->all();

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
}
