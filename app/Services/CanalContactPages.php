<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Throwable;

class CanalContactPages
{
    /** Read the supplied website and at most two contact pages linked from it. */
    public function read(string $url): array
    {
        $pages = [];
        try {
            $home = $this->fetch($url);
            if ($home === null) {
                return [];
            }
            $pages[] = ['url' => $home['url'], 'text' => $this->text($home['html'])];
            foreach ($this->contactLinks($home['html'], $home['url']) as $link) {
                $page = $this->fetch($link);
                if ($page !== null) {
                    $pages[] = ['url' => $page['url'], 'text' => $this->text($page['html'])];
                }
            }
        } catch (Throwable) {
            // Search remains available when the website is unavailable.
        }

        return $pages;
    }

    public function contactLinks(string $html, string $base): array
    {
        $links = [];
        foreach ($this->document($html)->getElementsByTagName('a') as $anchor) {
            $href = $anchor->getAttribute('href');
            if (! preg_match('/kontakt|contact/iu', $anchor->textContent.' '.$href)) {
                continue;
            }
            try {
                $url = (string) UriResolver::resolve(new Uri($base), new Uri($href))->withFragment('');
                if ($this->sameSite($base, $url) && $url !== $base && $this->validUrl($url)) {
                    $links[] = $url;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return array_slice(array_values(array_unique($links)), 0, 2);
    }

    private function document(string $html): DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new DOMDocument;
            $doc->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

            return $doc;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function text(string $html): string
    {
        $doc = $this->document($html);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//script|//style|//nav|//header|//noscript|//svg') as $node) {
            $node->parentNode->removeChild($node);
        }
        // Preserve separation between text in neighboring blocks.
        $text = html_entity_decode(strip_tags(str_replace('>', '> ', $doc->saveHTML())), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return mb_substr(trim(preg_replace('/\s+/u', ' ', $text)), 0, 20000);
    }

    private function sameSite(string $a, string $b): bool
    {
        return preg_replace('/^www\./', '', strtolower((string) parse_url($a, PHP_URL_HOST)))
            === preg_replace('/^www\./', '', strtolower((string) parse_url($b, PHP_URL_HOST)));
    }

    private function validUrl(string $url): bool
    {
        $parts = parse_url($url);

        return filter_var($url, FILTER_VALIDATE_URL) && is_array($parts)
            && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            && ! isset($parts['user']) && ! isset($parts['pass'])
            && (! isset($parts['port']) || $parts['port'] === (($parts['scheme'] ?? '') === 'https' ? 443 : 80));
    }

    /** Pin a validated public address; validate every redirect and cap response size. */
    protected function fetch(string $url): ?array
    {
        $original = $url;
        for ($redirect = 0; $redirect < 3; $redirect++) {
            if (! $this->validUrl($url) || ! $this->sameSite($original, $url)) {
                return null;
            }
            $host = parse_url($url, PHP_URL_HOST);
            $addresses = gethostbynamel($host) ?: [];
            if ($addresses === []) {
                return null;
            }
            foreach ($addresses as $address) {
                if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return null;
                }
            }
            $port = parse_url($url, PHP_URL_SCHEME) === 'https' ? 443 : 80;
            $body = '';
            $location = null;
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROXY => '',
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$addresses[0]}"],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'HlasCirkvi/1.0 (organization contact verification)',
                CURLOPT_WRITEFUNCTION => function ($handle, string $chunk) use (&$body) {
                    if (strlen($body) + strlen($chunk) > 1_000_000) {
                        return 0;
                    }
                    $body .= $chunk;

                    return strlen($chunk);
                },
                CURLOPT_HEADERFUNCTION => function ($handle, string $line) use (&$location) {
                    if (str_starts_with(strtolower($line), 'location:')) {
                        $location = trim(substr($line, 9));
                    }

                    return strlen($line);
                },
            ]);
            $ok = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            curl_close($curl);
            if ($ok === false) {
                return null;
            }
            if ($status >= 300 && $status < 400 && $location) {
                $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                continue;
            }

            return $status === 200 && str_contains(strtolower((string) $type), 'text/html')
                ? ['url' => $url, 'html' => $body] : null;
        }

        return null;
    }
}
