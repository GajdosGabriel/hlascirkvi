<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * HTML z editora, ktoré sa vypisuje na verejnom webe (popis kanála). Píše ho
 * správca kanála, teda ktorýkoľvek overený užívateľ — preto sa nechajú len
 * formátovacie značky a pri odkaze len bezpečná adresa. Všetko ostatné
 * (skripty, štýly, on* atribúty, javascript: odkazy) zahodí.
 */
class SafeHtml
{
    /** Značky, ktoré ostanú; ostatné sa rozbalia na svoj obsah. */
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'a', 'blockquote', 'h2', 'h3', 'h4', 'hr'];

    /** Značky, ktoré sa zahodia aj s obsahom. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'svg', 'math', 'head', 'title', 'noscript'];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="safe-html-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('safe-html-root');
        if (! $root) {
            return null;
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        // Prázdny editor posiela <p></p> či <p>&nbsp;</p> — to nie je popis.
        $text = trim(html_entity_decode(strip_tags($out), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\u{A0}");

        return $text === '' ? null : trim($out);
    }

    /**
     * Popis na vypísanie. Staré popisy (a tie, ktoré doplnila AI) sú čistý
     * text s novými riadkami — tie sa len escapujú.
     */
    public static function render(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }

        return self::isHtml($value) ? (string) self::clean($value) : nl2br(e($value), false);
    }

    /** Čistý text pre editor: odseky namiesto prázdnych riadkov. */
    public static function forEditor(?string $value): string
    {
        if ($value === null || trim($value) === '' || self::isHtml($value)) {
            return (string) $value;
        }

        return collect(preg_split('/\R{2,}/', trim($value)))
            ->map(fn (string $p) => '<p>' . nl2br(e(trim($p)), false) . '</p>')
            ->implode('');
    }

    public static function isHtml(string $value): bool
    {
        return (bool) preg_match('/<\s*\/?\s*[a-z][a-z0-9]*[\s>\/]/i', $value);
    }

    private static function walk(DOMNode $node): void
    {
        // Kópia zoznamu — deti sa počas prechodu vymieňajú.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if (! $child instanceof DOMElement) {
                // Komentáre, CDATA, spracovacie inštrukcie.
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child);

            if (! in_array($tag, self::TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            $href = $tag === 'a' ? trim($child->getAttribute('href')) : '';

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $child->removeAttribute($attribute->name);
            }

            if ($tag === 'a') {
                if (preg_match('~^(https?://|mailto:|tel:)~i', $href)) {
                    $child->setAttribute('href', $href);
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                } else {
                    // Odkaz bez bezpečnej adresy ostane len textom.
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                }
            }
        }
    }
}
