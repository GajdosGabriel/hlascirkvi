<?php

namespace App\Http\Controllers\Public;


use Youtube;
use App\Models\Post;
use App\Filters\PostFilters;
use App\Services\CreditUser;
use Illuminate\Http\Request;
use App\Events\VisitModel;
use App\Repositories\Contracts\PostRepository;
use App\Http\Controllers\Controller;






class PostController extends Controller
{

    public function __construct(PostRepository $postRepository)
    {
        $this->post = $postRepository;
    }



    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(30);

        return view('posts.index', compact('posts'));
    }



    public function show(Post $post, $slug, CreditUser $creditUser)
    {
        // Kanál(organization) nie je publikovaný a tým aj posts nie je možné zobrazovať.
        if (!$post->organization->published) {
            abort(405, "Kanál {$post->organization->title} je vypnutý!");
        }

        $creditUser->setPostHistory($post);

        event(new VisitModel($post));

        $posts = $this->post->postsBelongToOrganization($post->organization_id);

        return view('posts.show', ['post' => $post, 'posts'=> $posts]);
    }
}
