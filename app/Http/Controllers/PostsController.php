<?php

namespace App\Http\Controllers;


use Auth;
use Event;
use Youtube;
use App\Models\Post;
use Notification;
use App\Models\Messenger;
use Illuminate\Support\Str;
use App\Filters\PostFilters;
use App\Services\CreditUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\User\NotifyBell;
use App\Events\Posts\ViewCounter;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\PostSaveRequest;
use App\Http\Resources\CommentResource;
use Illuminate\Support\Facades\Storage;
use App\Services\TextCleaner\BodyCleaner;
use App\Repositories\Eloquent\EloquentPostRepository;




class PostsController extends Controller
{

    public function __construct()
    {
        $this->post = new EloquentPostRepository;
        $this->middleware('auth')->except('index', 'show' , 'showVideo');
    }

    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(28);
        return view('posts.index', compact('posts'));
    }



    public function show(Post $post, $slug, CreditUser $creditUser)
    {
        // Kanál(organization) nie je publikovaný a tým aj posts nie je možné zobrazovať.
        if(! $post->organization->published){
            abort(403, "Kanál {$post->organization->title} je vypnutý!");
        }

        $creditUser->setPostHistory($post);

//        session()->push('postsHistory',  $post );
//                session()->forget('postsHistory');
//        dd(\Session::get('postsHistory'));

        // event(new ViewCounter($post));


        $posts = $this->post->postsBelongToOrganization($post->organization_id);
        // $questions = (new Messenger)->scopeUserMessages($post->organization_id);


        return view('posts.show', ['post' => $post])
            // ->with('messages' , $questions ?? null)
            ->with('posts' , $posts);
    }


    public function showVideo($videoId) {

        return view('pages.show', compact('videoId'));
    }

    public function toBuffer(Post $post) {
        $post->updaters()->detach();
        return redirect('/')->with(session()->flash('flash', 'Video presunuté do Buffer!'));
    }

}
