<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\SaveCommentsRequest;
use App\Models\PendingComment;
use App\Services\PendingConfirmation;
use Illuminate\Validation\ValidationException;

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

    public function store(Post $post, SaveCommentsRequest $request, PendingConfirmation $confirmation)
    {
        $data = $request->commentData();
        $user = $request->user();

        // Prihlásený s overenou adresou komentuje hneď.
        if ($user?->hasVerifiedEmail()) {
            abort_if($user->banned(), 403, $user->accountAccessMessage());

            return new CommentResource($confirmation->publishComment($post, $user, $data));
        }

        // Ostatní čakajú, kým adresu nepotvrdia (Public\CommentConfirmationController).
        // Do `users` sa nezapisuje nič. Platí to aj pre adresu existujúceho
        // účtu — komentár je verejný pod menom účtu, takže bez potvrdenia by
        // ktokoľvek so znalosťou cudzieho e-mailu písal za iného.
        $email = $user?->email ?? $request->input('email');

        if (PendingComment::limitReached($email)) {
            throw ValidationException::withMessages([
                'email' => 'Na túto adresu už čakajú komentáre na potvrdenie. Skontrolujte, prosím, e-mail.',
            ]);
        }

        PendingComment::create($data + [
            'email' => $email,
            'post_id' => $post->getKey(),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ])->sendConfirmation();

        return response()->json(['pending' => true], 202);
    }

    public function destroy(Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return new CommentResource($comment);
    }
}
