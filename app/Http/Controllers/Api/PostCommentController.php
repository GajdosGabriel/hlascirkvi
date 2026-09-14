<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\SaveCommentsRequest;
use App\Notifications\Comments\CreatedNewComment;
use App\Notifications\Comments\RepliedToComment;
use App\Repositories\Eloquent\EloquentUserRepository;

class PostCommentController extends Controller
{

    public function index(Post $post)
    {
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with('replies')
            ->get();

        // CommentResource číta z commentable slug a titulok; všetky komentáre
        // aj odpovede patria tomuto príspevku, netreba ho pýtať pre každý zvlášť.
        $comments->each(function ($comment) use ($post) {
            $comment->setRelation('commentable', $post);
            $comment->replies->each->setRelation('commentable', $post);
        });

        return CommentResource::collection($comments);
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

        // Autor komentára, na ktorý sa odpovedá. Anonymné komentáre (user_id
        // 100) patria spoločnému účtu, tomu nemá zmysel nič posielať.
        $parentAuthor = $comment->parent_id ? Comment::find($comment->parent_id)?->user : null;

        if ($parentAuthor && $parentAuthor->id !== 100 && $parentAuthor->id !== (int) $comment->user_id) {
            $parentAuthor->notify(new RepliedToComment($comment));
        }

        if ($owner && $owner->id !== (int) $comment->user_id && $owner->id !== $parentAuthor?->id) {
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
