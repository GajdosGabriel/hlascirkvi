<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Services\CommentModeration;
use Illuminate\Console\Command;

class CommentsModerate extends Command
{
    protected $signature = 'comments:moderate
                            {--all : Prejsť aj komentáre, ktoré už moderáciou prešli (po zmene pravidiel)}';
    protected $description = 'Dodatočne skryje nevhodné komentáre a upozorní ich autorov';

    public function handle(CommentModeration $moderation): int
    {
        $count = 0;
        // Nové a upravené komentáre moderuje CommentObserver hneď pri uložení
        // a označí ich `moderated_at`; sem zostanú len staré a zapísané mimo
        // Eloquentu. Po zmene pravidiel treba --all.
        $query = Comment::without('favorites')->whereNull('moderation_reason');
        if (! $this->option('all')) {
            $query->whereNull('moderated_at');
        }

        $query->chunkById(200, function ($comments) use ($moderation, &$count) {
            foreach ($comments as $comment) {
                if ($reason = $moderation->reason($comment->body)) {
                    $comment->forceFill(['moderation_reason' => $reason, 'published' => null, 'reply_to_guest' => false])->save();
                    $count++;
                } elseif ($comment->moderated_at === null) {
                    Comment::whereKey($comment->id)->toBase()->update(['moderated_at' => now()]);
                }
            }
        });
        $this->info("Skryté komentáre: {$count}");
        return self::SUCCESS;
    }
}
