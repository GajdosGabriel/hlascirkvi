<?php

namespace App\Services\Youtube;

/**
 * Chyba YouTube Data API. Správa drží tvar, v akom ju vracal balík
 * alaouy/youtube („Error 403 … : quotaExceeded"), aby staré kontroly cez
 * str_contains platili ďalej; `reason` je strojový dôvod z odpovede.
 */
class YoutubeApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $reason = null,
        public readonly int $status = 0,
    ) {
        parent::__construct($message, $status);
    }

    public function is(string ...$reasons): bool
    {
        return in_array($this->reason, $reasons, true);
    }
}
