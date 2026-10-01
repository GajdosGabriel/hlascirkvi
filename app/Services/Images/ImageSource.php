<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Bajty obrázka spolu s tým, odkiaľ prišli. Existuje preto, aby StoreImage
 * nemuselo rozlišovať medzi nahratým súborom a adresou – ďalej ide oboma
 * cestami tá istá binárka.
 */
final class ImageSource
{
    private function __construct(
        public readonly string $binary,
        public readonly string $originalName,
    ) {
    }

    public static function fromUpload(UploadedFile $file): self
    {
        $binary = @file_get_contents($file->getRealPath());

        if ($binary === false || $binary === '') {
            throw new RuntimeException('Nahratý súbor sa nepodarilo prečítať.');
        }

        return new self($binary, (string) $file->getClientOriginalName());
    }

    /**
     * Intervention 4 už vzdialené adresy nečíta, takže bajty ťaháme sami.
     * Zároveň je to jediné miesto, kde sa dá obmedziť, kam smie server siahať.
     */
    public static function fromUrl(string $url): self
    {
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new RuntimeException("Adresa obrázka nemá povolenú schému: {$url}");
        }

        if (! in_array($host, config('images.fetch.allowed_hosts', []), true)) {
            throw new RuntimeException("Obrázky sa z hostiteľa {$host} nesťahujú.");
        }

        $maxBytes = (int) config('images.fetch.max_bytes');

        // Presmerovanie by obišlo zoznam allowed_hosts. Telo sa číta po
        // prúde, aby sa neťahal súbor väčší než povolený limit.
        $response = Http::timeout(config('images.fetch.timeout'))
            ->retry(config('images.fetch.retries'), 250)
            ->withoutRedirecting()
            ->withOptions(['stream' => true])
            ->get($url)
            ->throw();

        if ((int) $response->header('Content-Length') > $maxBytes) {
            throw new RuntimeException("Obrázok z {$url} je väčší než povolených {$maxBytes} B.");
        }

        $stream = $response->toPsrResponse()->getBody();
        $binary = '';

        while (! $stream->eof()) {
            $binary .= $stream->read(65536);

            if (strlen($binary) > $maxBytes) {
                throw new RuntimeException("Obrázok z {$url} je väčší než povolených {$maxBytes} B.");
            }
        }

        if ($binary === '') {
            throw new RuntimeException("Adresa {$url} vrátila prázdnu odpoveď.");
        }

        return new self($binary, basename((string) parse_url($url, PHP_URL_PATH)));
    }
}
