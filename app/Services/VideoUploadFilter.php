<?php

namespace App\Services;

use App\Models\Canal;

/**
 * Obmedzenie importu na videá s určitými slovami v titulku. Pravidlo drží
 * kanál v stĺpci `video_title_include` (jedna fráza na riadok); bez neho
 * filter nič neodmieta.
 */
class VideoUploadFilter
{
    public $canal;
    public $title;

    public function __construct(Canal $canal, $title)
    {
        $this->canal = $canal;
        $this->title = $title;
    }

    /** True, keď má byť video odmietnuté. */
    public function wordsChecker(): bool
    {
        $words = $this->getAcceptedWords();

        return $words !== [] && ! $this->containsAny($words);
    }

    /** @return string[] */
    public function getAcceptedWords(): array
    {
        $lines = preg_split('/\R/u', (string) $this->canal->video_title_include) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }

    private function containsAny(array $words): bool
    {
        $title = mb_strtolower((string) $this->title);

        foreach ($words as $word) {
            if (str_contains($title, mb_strtolower($word))) {
                return true;
            }
        }

        return false;
    }
}
