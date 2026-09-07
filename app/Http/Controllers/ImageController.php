<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Post;

class ImageController extends Controller
{
    /**
     * Obrázok patrí príspevku a ten patrí kanálu. Kým sa tu nekontrolovalo nič,
     * ktorýkoľvek prihlásený užívateľ vedel zmazať obrázok cudzieho príspevku.
     *
     * Väzba je morphTo (`fileable`), ale v tabuľke sú len dva typy: App\Models\Post
     * a App\Models\Event po zrušenej agende podujatí — a tá trieda už neexistuje,
     * takže sa na ňu nedá naviazať policy. Preto explicitná kontrola typu.
     */
    public function destroy(Image $image)
    {
        abort_unless($image->fileable_type === Post::class, 404);

        $post = Post::withTrashed()->find($image->fileable_id);

        abort_if($post === null, 404);

        $this->authorize('update', $post);

        $image->delete();
    }
}
