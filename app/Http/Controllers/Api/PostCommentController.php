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
        $comment->update($request->only('body'));

        return new CommentResource($comment);
    }

    public function store(Post $post, SaveCommentsRequest $saveComments)
    {

        if ($saveComments->email) {
            (new EloquentUserRepository)->checkIfUserAccountExist($saveComments);
        }

        $comment = $saveComments->save($post);

        if (!$comment->user_id == auth()->user()->org_id) {
            $comment->user->notify(new CreatedNewComment($comment));
        }

        return new CommentResource($comment);
    }

    public function destroy(Post $post, Comment $comment)
    {
        $comment->delete();

        return new CommentResource($comment);
    }
}
