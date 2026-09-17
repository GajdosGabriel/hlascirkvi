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
        public readonly ?string $detail = null,
    ) {
        parent::__construct($message, $status);
    }

    public function is(string ...$reasons): bool
    {
        return in_array($this->reason, $reasons, true);
    }

    /**
     * YouTube odmieta samotný kľúč: neplatný, expirovaný či obmedzený kľúč
     * alebo vypnuté API. Bez zásahu správcu sa to neopraví.
     *
     * Neplatný kľúč má v `errors[].reason` len všeobecné „badRequest",
     * presný dôvod (API_KEY_INVALID…) nesie `detail` z google.rpc.ErrorInfo.
     */
    public function isKeyFailure(): bool
    {
        return $this->is('accessNotConfigured', 'keyInvalid', 'keyExpired')
            || str_starts_with((string) $this->detail, 'API_KEY_')
            || $this->detail === 'SERVICE_DISABLED'
            || str_contains($this->getMessage(), 'API key not valid');
    }

    /**
     * Ďalšie dopyty by zlyhali rovnako — vyčerpaná kvóta alebo odmietnutý
     * kľúč. Dávkový beh sa má ukončiť.
     */
    public function stopsRun(): bool
    {
        return $this->is('quotaExceeded', 'dailyLimitExceeded') || $this->isKeyFailure();
    }
}
