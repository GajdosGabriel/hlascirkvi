<?php

namespace App\Services;

use Illuminate\Support\Str;

class CommentModeration
{
    public function reason(string $body): ?string
    {
        $text = Str::ascii(mb_strtolower(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $text = preg_replace('/\s+/', ' ', $text);
        foreach (config('comment_moderation.patterns') as $reason => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $text)) {
                    return $reason;
                }
            }
        }
        return null;
    }
}
