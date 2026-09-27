<?php

namespace App\Services;

/** Local extractive ranking scans the whole transcript without spending API tokens. */
class TranscriptHighlights
{
    public function select(string $text, int $budget): string
    {
        if (strlen($text) <= $budget) {
            return $text;
        }

        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $chunks = array_map(fn ($words) => implode(' ', $words), array_chunk($words, 100));
        $terms = [];
        $frequency = [];
        foreach ($chunks as $index => $chunk) {
            preg_match_all('/\p{L}{4,}/u', mb_strtolower($chunk), $matches);
            $terms[$index] = array_unique($matches[0]);
            foreach ($terms[$index] as $term) {
                $frequency[$term] = ($frequency[$term] ?? 0) + 1;
            }
        }

        $scores = [];
        foreach ($chunks as $index => $chunk) {
            $score = 0;
            foreach ($terms[$index] as $term) {
                $score += log(1 + count($chunks) / $frequency[$term]);
            }
            // Prefer developed ideas, concrete stories and questions over greetings or promotion.
            $score /= sqrt(max(1, count(preg_split('/\s+/u', $chunk))));
            $score += 3 * min(4, preg_match_all('/príbeh|napríklad|pretože|prečo|pochop|odpust|rozhod|zmenil|skúsen|svedect|\?/iu', $chunk));
            $score -= 5 * preg_match_all('/vitajte|vítam|odber|prihláste|subscribe|www\.|https?:|číslo účtu/iu', $chunk);
            $scores[$index] = $score;
        }
        arsort($scores, SORT_NUMERIC);

        $selected = [];
        $remaining = $budget;
        foreach ($scores as $index => $score) {
            $separator = $selected === [] ? 0 : strlen("\n\n[…]\n\n");
            if ($remaining <= $separator) {
                break;
            }
            $excerpt = mb_strcut($chunks[$index], 0, $remaining - $separator, 'UTF-8');
            if (trim($excerpt) === '') {
                continue;
            }
            $selected[$index] = $excerpt;
            $remaining -= strlen($excerpt) + $separator;
        }
        ksort($selected);

        return implode("\n\n[…]\n\n", $selected);
    }
}
