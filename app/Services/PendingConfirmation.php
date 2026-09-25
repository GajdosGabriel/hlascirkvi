<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\PendingComment;
use App\Models\PendingFavorite;
use App\Models\PendingPrayer;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Comments\CreatedNewComment;
use App\Notifications\Comments\RepliedToComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Potvrdenie adresy z čakárne (PendingPrayer, PendingComment, PendingFavorite).
 *
 * Kliknutím na ktorýkoľvek odkaz autor preukázal adresu, preto sa zverejní
 * všetko, čo s ňou čaká — modlitby aj komentáre.
 */
class PendingConfirmation
{
    public function __construct(protected UserActivation $activation)
    {
    }

    /**
     * Vráti overený účet autora, alebo null, ak účet s touto adresou nie je
     * aktívny (zmazaný, zablokovaný) — vtedy sa čakajúce záznamy zahodia.
     *
     * @param  PendingPrayer|PendingComment  $pending
     */
    public function confirm(Model $pending): ?User
    {
        $email = $pending->email;
        $existing = User::withTrashed()->whereEmail($email)->first();

        if ($existing && ($existing->trashed() || $existing->banned())) {
            PendingPrayer::where('email', $email)->delete();
            PendingComment::where('email', $email)->delete();
            PendingFavorite::where('email', $email)->delete();

            return null;
        }

        return DB::transaction(function () use ($email) {
            $user = $this->activation->verifiedUserFor($email);

            foreach (PendingPrayer::forEmail($email)->get() as $row) {
                $this->activation->publishPrayer($user, $row->only(['title', 'body', 'user_name']));
                $row->delete();
            }

            foreach (PendingComment::forEmail($email)->with('post')->orderBy('id')->get() as $row) {
                // Príspevok medzitým mohol zmiznúť.
                if ($row->post) {
                    $this->publishComment($row->post, $user, $row->only(['body', 'parent_id']));
                }
                $row->delete();
            }

            foreach (PendingFavorite::forEmail($email)->get() as $row) {
                // Označenie sa len pridá — ak ho užívateľ medzitým má, nič
                // sa neprepína (toggleFavorite by ho zrušil).
                $model = $row->favorited;
                if ($model && ! $model->favorites()->whereUserId($user->id)->exists()) {
                    $model->favorites()->create(['user_id' => $user->id]);
                }
                $row->delete();
            }

            return $user;
        });
    }

    /**
     * „Pripojiť sa k modlitbe" / odber kanála od neprihláseného: čaká, kým
     * autor nepotvrdí adresu. Rovnaké označenie, ktoré už čaká, druhý e-mail
     * neposiela.
     */
    public function queueFavorite(Model $model, string $email, Request $request): void
    {
        $pending = PendingFavorite::firstOrNew([
            'email' => $email,
            'favorited_type' => $model->getMorphClass(),
            'favorited_id' => $model->getKey(),
        ]);

        if ($pending->exists && $pending->expires_at?->isFuture()) {
            return;
        }

        if (PendingFavorite::limitReached($email)) {
            throw ValidationException::withMessages([
                'email' => 'Na túto adresu už čaká viac žiadostí na potvrdenie. Skontrolujte, prosím, e-mail.',
            ]);
        }

        $pending->forceFill([
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'sent_at' => null,
            'reminded_at' => null,
        ])->sendConfirmation();
    }

    /**
     * Uloží komentár pod príspevok a upovedomí autora komentára, na ktorý sa
     * odpovedá, a správcu kanála.
     *
     * @param  array{body: string, parent_id?: int|null}  $data
     */
    public function publishComment(Post $post, User $user, array $data): Comment
    {
        // Komentár, na ktorý sa odpovedalo, mohol byť medzitým zmazaný —
        // odpoveď potom ostane ako hlavný komentár.
        if (! empty($data['parent_id']) && ! $post->comments()->published()->whereKey($data['parent_id'])->exists()) {
            $data['parent_id'] = null;
        }

        $comment = $post->comments()->create($data + ['user_id' => $user->id]);

        // Odpoveď na komentár hosťa (napr. z YouTube) si poznačíme,
        // aby sme na ňu zareagovali aj na YouTube.
        if ($comment->parent_id && Comment::find($comment->parent_id)?->fromYoutube()) {
            $comment->forceFill(['reply_to_guest' => true])->save();
        }

        // Pôvodne `if (!$comment->user_id == auth()->user()->canal_id)`. `!` sa
        // vyhodnotí skôr než `==`, takže sa porovnávalo `false` s canal_id, a pre
        // neprihláseného návštevníka to navyše siahalo na null. Zmysel je
        // upovedomiť správcu kanála, ak nekomentoval sám sebe.
        $owner = $post->canal?->user;

        // Autor komentára, na ktorý sa odpovedá. Anonymné komentáre (user_id
        // 100) patria spoločnému účtu, tomu nemá zmysel nič posielať.
        $parentAuthor = $comment->parent_id ? Comment::find($comment->parent_id)?->user : null;

        if ($parentAuthor && $parentAuthor->id !== 100 && $parentAuthor->id !== (int) $comment->user_id) {
            $parentAuthor->notify(new RepliedToComment($comment));
        }

        if ($owner && $owner->id !== (int) $comment->user_id && $owner->id !== $parentAuthor?->id) {
            $owner->notify(new CreatedNewComment($comment));
        }

        return $comment;
    }
}
