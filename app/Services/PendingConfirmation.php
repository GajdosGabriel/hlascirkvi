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
use App\Services\Youtube\CommentSync;
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
                    $this->publishComment($row->post, $user, $row->only(['body', 'parent_id', 'reply_to_id']));
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
     * odpovedá, a všetkých správcov kanála.
     *
     * @param  array{body: string, parent_id?: int|null, reply_to_id?: int|null}  $data
     */
    public function publishComment(Post $post, User $user, array $data): Comment
    {
        // Hlavný komentár vlákna sa načíta raz; mohol byť medzitým zmazaný
        // alebo skrytý — odpoveď potom ostane ako hlavný komentár.
        $parent = ! empty($data['parent_id'])
            ? $post->comments()->published()->with('user')->find($data['parent_id'])
            : null;

        if (! $parent) {
            $data['parent_id'] = null;
            $data['reply_to_id'] = null;
        }

        // Komentár, na ktorý sa priamo odpovedalo (iná odpoveď vo vlákne).
        // Ak zmizol, odpoveď ostane pod hlavným komentárom.
        $target = $parent;

        if ($parent && ! empty($data['reply_to_id']) && (int) $data['reply_to_id'] !== $parent->id) {
            $target = $post->comments()->published()->with('user')->find($data['reply_to_id']);
            $data['reply_to_id'] = $target?->id ?? $parent->id;
            $target ??= $parent;
        } elseif ($parent) {
            $data['reply_to_id'] = $parent->id;
        }

        $comment = $post->comments()->create($data + ['user_id' => $user->id]);

        if ($comment->published === null) {
            return $comment;
        }

        // Odpoveď na komentár hosťa (napr. z YouTube) si poznačíme,
        // aby sme na ňu zareagovali aj na YouTube.
        if ($parent?->fromYoutube()) {
            $comment->forceFill(['reply_to_guest' => true])->save();
        }

        // Kto už o komentári vie — autor a ten, komu prišlo RepliedToComment.
        $informed = [(int) $comment->user_id];

        // Autor komentára, na ktorý sa priamo odpovedá. Anonymné komentáre
        // patria spoločnému účtu, tomu nemá zmysel nič posielať.
        $parentAuthor = $target?->user;

        if ($parentAuthor && $parentAuthor->id !== CommentSync::USER_ID && ! in_array($parentAuthor->id, $informed, true)) {
            $parentAuthor->notify(new RepliedToComment($comment));
            $informed[] = $parentAuthor->id;
        }

        // Všetci správcovia kanála (Canal::users()), nielen ten, kto má kanál
        // práve aktívny (Canal::user() cez users.canal_id).
        $admins = $post->canal?->users()->whereNotIn('users.id', $informed)->get() ?? collect();

        foreach ($admins as $admin) {
            $admin->notify(new CreatedNewComment($comment));
        }

        return $comment;
    }
}
