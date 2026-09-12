<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Services\Buffer;
use App\Models\Canal;
use App\Repositories\Contracts\PostRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BufferController extends Controller
{
    protected $posts;
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware(['auth', 'checkAdmin']);
    }

    public function index(Request $request, Buffer $buffer)
    {

        $posts = Post::unpublished()->latest();

        if ($request->posts) {
            $posts = $posts->where('organization_id', $request->posts);
        }

        return view(
            'admins.buffer.index',
            [
                'posts' => $posts->paginate(32),
                // Bočný zoznam potrebuje len názov kanála a počet čakajúcich
                // príspevkov. Pôvodné $posts->get()->groupBy() na to načítalo
                // všetky nezverejnené príspevky aj s obrázkami a kanálmi.
                'organizations' => $this->organizationsWithUnpublishedPosts(),
                // Kedy dnes publisher vypustí ďalší príspevok.
                'status' => $buffer->status(),
            ]
        );
    }

    /**
     * Bočný zoznam kanálov s počtom čakajúcich príspevkov.
     *
     * Skúšal som to prepísať na jedno GROUP BY nad `posts`, ale vyšlo to
     * štyrikrát pomalšie: s globálnym scope `youtube_blocked` si optimalizátor
     * vyberie index podľa neho a zvyšných ~20-tisíc riadkov musí zoradiť.
     * Korelovaný poddopyt sa vyhodnotí len pre kanály, ktoré prejdú whereHas.
     */
    protected function organizationsWithUnpublishedPosts()
    {
        $unpublished = fn ($query) => $query->unpublished();

        return Canal::whereHas('posts', $unpublished)
            ->withCount(['posts as unpublished_posts_count' => $unpublished])
            ->orderBy('title')
            ->get(['id', 'title']);
    }
}
