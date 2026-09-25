<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Services\CommentModeration;
use Illuminate\Console\Command;

class CommentsModerate extends Command
{
    protected $signature = 'comments:moderate';
    protected $description = 'Dodatočne skryje nevhodné komentáre a upozorní ich autorov';

    public function handle(CommentModeration $moderation): int
    {
        $count = 0;
        Comment::without('favorites')->whereNull('moderation_reason')->chunkById(200, function ($comments) use ($moderation, &$count) {
            foreach ($comments as $comment) {
                if ($reason = $moderation->reason($comment->body)) {
                    $comment->forceFill(['moderation_reason' => $reason, 'published' => null, 'reply_to_guest' => false])->save();
                    $count++;
                }
            }
        });
        $this->info("Skryté komentáre: {$count}");
        return self::SUCCESS;
    }
}
