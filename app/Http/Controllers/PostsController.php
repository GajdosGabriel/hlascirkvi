<?php

namespace App\Http\Controllers;


use App\Events\Posts\ViewCounter;
use App\Http\Resources\PostResource;
use App\Messenger;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Services\CreditUser;
use App\Services\TextCleaner\BodyCleaner;
use Auth;
use Event;
use Illuminate\Http\Response;
use App\Post;
use App\Filters\PostFilters;
use Illuminate\Http\Request;
use App\Http\Requests\PostSaveRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Notification;
use Youtube;




class PostsController extends Controller
{

    public function __construct()
    {
        $this->post = new EloquentPostRepository;
        $this->middleware('auth')->except('index', 'show' , 'showVideo', 'recommended');
    }

    public function index(PostFilters $filters)
    {
        $posts = $this->post->postsByUpdater(15)->filter($filters)->paginate(28);

        return view('posts.index', compact('posts'));
//        return view('posts.index');
    }



    public function show(Post $post, $slug, CreditUser $creditUser)
    {
//        $cleanText = new CleanBodyText($post);
//       $xxx = $cleanText->detect();
////       $cleanText->save();
//
        $creditUser->setPostHistory($post);

//        session()->push('postsHistory',  $post );
//                session()->forget('postsHistory');
//        dd(\Session::get('postsHistory'));

        event(new ViewCounter($post));

        $posts = $this->post->postsBelongToOrganization($post->organization_id);
        $questions = (new Messenger)->scopeUserMessages($post->organization_id);

//        return $post;
        return view('posts.show', ['post' => $post])
            ->with('messages' , $questions ?? null)
            ->with('posts' , $posts);
    }


    public function showVideo($videoId) {

        return view('pages.show', compact('videoId'));
    }

    public function create() {
        return view('posts.create', ['post' => new Post]);
    }


    public function edit($post, $slug)
    {
        $post = $this->post->find($post);
        $this->authorize('update', $post);
        return view('posts.edit', ['post' => $post]);
    }


    public function store(PostSaveRequest $request)
    {
        $request->save();
        return redirect()->route('posts.index');
    }


    public function update(PostSaveRequest $request, $post)
    {
        $post = $request->update($post);
        return redirect()->route('post.show', [$post->id, $post->slug]);
    }

    public function delete(Post $post) {
        $this->authorize('update', $post);
        $post->delete();
        return redirect('/')->with(session()->flash('flash', 'Príspevok bol zmazaný!'));
    }

    public function destroy(Post $post) {
        $this->authorize('update', $post);
//        $post->deleteImages();
//
        $post->forceDelete();

        return redirect('/')->with(session()->flash('flash', 'Príspevok bol trvale odstranený!'));
    }


    public function toBuffer(Post $post) {
        $post->updaters()->detach();
        return redirect('/')->with(session()->flash('flash', 'Video presunuté do Buffer!'));
    }

}
