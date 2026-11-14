<?php

use App\Enums\PageType;

// Detect typeMenu accourding to pageRouteName
function typePage()
{
    if (Str::contains( request()->route()->getName(), PageType::Admin->value)) {
        return PageType::Admin->value;
    } elseif (Str::contains( request()->route()->getName(), PageType::Profile->value)) {
        return PageType::Profile->value;
    } else {
        return  PageType::Public->value;
    };
}

// Remove HardSpace text
function cleanHardSpace($text)
{
    $text = str_replace("&quot;", '', $text);
    $text = str_replace("<br>", '', $text);
    return  $text;
}

// Clean body text
function cleanBody($bodyText)
{
    // New line is required to split non-blank lines
    return preg_replace('~\\s?<p>(\\s|&nbsp;)+</p>\\s?~', '', $bodyText);
}

// Clean title text
function cleanTitle($titleText)
{
    // Decode entities and remove &nbsp space characters
    $titleText = str_replace("&quot;", '"', $titleText);
    $titleText = str_replace("&amp;", '&', $titleText);
    $titleText = str_replace("&nbsp;", ' ', $titleText);
    // Hashtagy a podčiarkovníky z YouTube importu (`Názov_Ivanka #ps#sr`);
    // `#5` ostáva, je to číslo, nie hashtag.
    $titleText = preg_replace('/#\p{L}[\p{L}\p{N}_]*/u', '', $titleText);
    $titleText = str_replace('_', ' ', $titleText);
    // Reome white space
    $titleText = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $titleText);
    return \App\Support\TitleNormalizer::clean(trim($titleText));
}

if (! function_exists('csp_nonce')) {
    /**
     * Nonce aktuálnej požiadavky pre inline <script> a <style> (CSP).
     * Mimo požiadavky cez SecurityHeaders (napr. chybové stránky) je prázdny.
     */
    function csp_nonce(): string
    {
        return app()->bound('csp.nonce') ? app('csp.nonce') : '';
    }
}
