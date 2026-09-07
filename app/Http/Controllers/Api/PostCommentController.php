<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\SaveCommentsRequest;
use App\Notifications\Comments\CreatedNewComment;
use App\Repositories\Eloquent\EloquentUserRepository;

class PostCommentController extends Controller
{

    public function index(Post $post)
    {
        return CommentResource::collection($post->comments);
    }

    public function update(Post $post, Comment $comment, SaveCommentsRequest $request)
    {
        $this->authorize('update', $comment);

        $comment->update($request->only('body'));

        return new CommentResource($comment);
    }

    public function store(Post $post, SaveCommentsRequest $saveComments)
    {

        if ($saveComments->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($saveComments);
        }

        $comment = $saveComments->save($post);

        // Pôvodne `if (!$comment->user_id == auth()->user()->org_id)`. `!` sa
        // vyhodnotí skôr než `==`, takže sa porovnávalo `false` s org_id, a pre
        // neprihláseného návštevníka to navyše siahalo na null. Zmysel je
        // upovedomiť správcu kanála, ak nekomentoval sám sebe.
        $owner = $post->organization?->user;

        if ($owner && $owner->id !== (int) $comment->user_id) {
            $owner->notify(new CreatedNewComment($comment));
        }

        return new CommentResource($comment);
    }

    public function destroy(Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return new CommentResource($comment);
    }
}
