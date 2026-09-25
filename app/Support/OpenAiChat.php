<?php

namespace App\Support;

use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Throwable;

/**
 * Parametre chat volania podľa modelu. Novšie modely (gpt-5 a ďalej, napr.
 * gpt-6-luna) neberú `max_tokens` ani vlastnú `temperature` — len predvolenú.
 * `max_completion_tokens` berú aj staré gpt-4o/4.1, preto platí pre všetky.
 */
class OpenAiChat
{
    /** Staré modely, ktoré ešte berú vlastnú teplotu. */
    public static function supportsTemperature(string $model): bool
    {
        return (bool) preg_match('/^(gpt-4|gpt-3\.5)/', $model);
    }

    /**
     * Novšie modely pred odpoveďou „rozmýšľajú“ a tie tokeny sa rátajú do
     * stropu — bez rezervy by odpoveď vyšla prázdna. Strop je len strop,
     * platí sa za skutočne použité tokeny.
     */
    public static function params(string $model, int $maxTokens, float $temperature): array
    {
        if (static::supportsTemperature($model)) {
            return ['model' => $model, 'max_completion_tokens' => $maxTokens, 'temperature' => $temperature];
        }

        return ['model' => $model, 'max_completion_tokens' => $maxTokens * 3];
    }

    /**
     * Chat volanie s jedným opakovaním pri sieťovej chybe (DNS, spojenie).
     * Chyby API (zlý kľúč, limit) sa neopakujú — druhý pokus by dopadol rovnako.
     */
    public static function create(array $params): CreateResponse
    {
        return retry(2, fn () => OpenAI::chat()->create($params), 2000,
            fn (Throwable $e) => $e instanceof TransporterException);
    }
}
