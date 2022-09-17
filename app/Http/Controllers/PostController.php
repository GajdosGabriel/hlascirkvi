<?php

namespace App\Http\Controllers;


use Youtube;
use App\Models\Post;
use App\Filters\PostFilters;
use App\Services\CreditUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\VisitModel;
use App\Repositories\Contracts\PostRepository;






class PostController extends Controller
{

    public function __construct(PostRepository $postRepository)
    {
        $this->post = $postRepository;
        $this->middleware('auth')->except('index', 'show', 'showVideo');
    }

    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(28);

        return view('posts.index', compact('posts'));
    }



    public function show(Post $post, $slug, CreditUser $creditUser)
    {
        // Kanál(organization) nie je publikovaný a tým aj posts nie je možné zobrazovať.
        if (!$post->organization->published) {
            abort(405, "Kanál {$post->organization->title} je vypnutý!");
        }

        $creditUser->setPostHistory($post);

        //        session()->push('postsHistory',  $post );
        //                session()->forget('postsHistory');
        //        dd(\Session::get('postsHistory'));

        event(new VisitModel($post));

        $posts = $this->post->postsBelongToOrganization($post->organization_id);
        // $questions = (new Messenger)->scopeUserMessages($post->organization_id);


        return view('posts.show', ['post' => $post])
            // ->with('messages' , $questions ?? null)
            ->with('posts', $posts);
    }
}
