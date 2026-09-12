<?php

namespace App\Services\Prayers;

/**
 * Rozpoznáva reklamný spam medzi prosbami o modlitbu.
 *
 * Samotné kľúčové slovo nestačí — „pôžička", „urgentne" aj „pozícia" sú
 * v skutočných prosbách úplne bežné („aby si R.G. prevzal svoju pôžičku",
 * „urgentne prosím o modlitby"). Preto každé pravidlo vyžaduje kombináciu
 * signálov, nie jedno slovo.
 */
class SpamDetector
{
    public const REASON_LOAN = 'pôžičkový / finančný spam';
    public const REASON_SCAM = 'reklama na „zázračné" služby';
    public const REASON_LINK = 'príspevok bez textu, len odkaz alebo kontakt';

    /** Slovník požičiavania a peňazí. */
    private const MONEY = [
        'pozick', 'pujck', 'uver', 'urok', 'kapital', 'splacan', 'splatnost', 'konsolidac',
        'loan', 'credit', 'kredit', 'financni', 'financna pomoc', 'bitcoin', 'kryptomen',
    ];

    /** Predajná formulácia — spamer niečo ponúka a chce odpoveď. */
    private const OFFER = [
        'ponukam', 'ponukame', 'ponuka', 'ponuknite', 'nabizim', 'nabizime', 'nabidka',
        'poskytujem', 'poskytujeme', 'potrebujete', 'ziskajte', 'ziskejte', 'ziskat',
        'kontaktujte', 'poziadajte', 'prihlasit', 'seriozn', 'garantovan', 'bez rucitel',
        'bez zaruky', 'do 24 hodin', 'apply', 'contact us', 'offer', 'we provide',
    ];

    /** Podvody typu „zosielateľ kúziel" a „zázračné uzdravenie". */
    private const SCAM = [
        'kuziel', 'kuzlo', 'kuzla', 'kuzel', 'kouzl', 'zosielatel',
        'voodoo', 'spell caster', 'love spell', 'bylinkar', 'herpes', 'traditional healer',
    ];

    /** TLD, na ktorých poznáme odkaz aj bez schémy. */
    private const TLD = 'sk|cz|com|net|org|eu|info|biz|ru|pl|hu|at|de|uk|io|link|online|site|xyz|me|tv';

    /**
     * @return array{reason: string, signals: array<int, string>}|null
     */
    public function inspect(?string $title, string $body): ?array
    {
        $raw = trim(($title ?? '') . "\n" . $body);
        $text = $this->normalize($raw);
        $contact = $this->contacts($raw);

        // Bez kontaktu nemá reklama zmysel — odkaz alebo e-mail je jej pointa.
        if ($contact !== []) {
            $money = $this->hits($text, self::MONEY);
            $offer = $this->hits($text, self::OFFER);

            if ($money !== [] && $offer !== []) {
                return [
                    'reason' => self::REASON_LOAN,
                    'signals' => array_merge(
                        array_slice($money, 0, 3),
                        array_slice($offer, 0, 3),
                        array_slice($contact, 0, 2),
                    ),
                ];
            }

            $scam = $this->hits($text, self::SCAM);

            if ($scam !== []) {
                return [
                    'reason' => self::REASON_SCAM,
                    'signals' => array_merge(array_slice($scam, 0, 3), array_slice($contact, 0, 2)),
                ];
            }
        }

        // Len odkaz alebo e-mail bez prosby. Pozeráme výhradne na telo — titulok
        // je takmer vždy predvyplnené „Prosba o modlitbu" a sám by stačil na to,
        // aby holý odkaz vyzeral ako text. Prosba, ktorá odkaz iba prikladá, tu
        // neprepadne: po odstránení URL v nej zostane text.
        if ($this->isBareLink($body)) {
            return ['reason' => self::REASON_LINK, 'signals' => $contact ?: ['odkaz']];
        }

        return null;
    }

    public function isSpam(?string $title, string $body): bool
    {
        return $this->inspect($title, $body) !== null;
    }

    /**
     * Malé písmená bez diakritiky. Otáznik zahadzujeme, lebo starší import
     * nechal v texte mojibake („pôži??iek"); inak by sa časť spamu nenašla.
     *
     * @return string text obalený medzerami, aby sa dal hľadať cez str_contains
     */
    private function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, [
            'á' => 'a', 'ä' => 'a', 'â' => 'a', 'à' => 'a', 'č' => 'c', 'ć' => 'c', 'ď' => 'd',
            'é' => 'e', 'ě' => 'e', 'ë' => 'e', 'è' => 'e', 'í' => 'i', 'ì' => 'i', 'ĺ' => 'l',
            'ľ' => 'l', 'ł' => 'l', 'ň' => 'n', 'ń' => 'n', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ő' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'š' => 's', 'ś' => 's', 'ť' => 't', 'ú' => 'u',
            'ů' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
            'ç' => 'c', 'î' => 'i', 'û' => 'u', 'ê' => 'e',
        ]);
        $s = str_replace('?', '', $s);
        $s = preg_replace('/[^a-z0-9]+/u', ' ', $s);

        return ' ' . trim($s) . ' ';
    }

    /**
     * @param  array<int, string>  $needles
     * @return array<int, string>
     */
    private function hits(string $text, array $needles): array
    {
        return array_values(array_filter($needles, fn ($n) => str_contains($text, $n)));
    }

    /**
     * E-mail, odkaz, messenger alebo telefón.
     *
     * @return array<int, string>
     */
    private function contacts(string $raw): array
    {
        $found = [];
        $joined = $this->joinSpacedDots($raw);

        if (preg_match('~[\p{L}\d._%+-]+@[\p{L}\d.-]+\.[\p{L}]{2,}~u', $joined)) {
            $found[] = 'e-mail';
        }

        if (preg_match($this->urlPattern(), $joined)) {
            $found[] = 'odkaz';
        }

        if (preg_match('~whats\s?app|viber|telegram~iu', $raw)) {
            $found[] = 'messenger';
        }

        if (preg_match('~\+\s?\d[\d\s]{7,}~u', $raw)) {
            $found[] = 'telefón';
        }

        return $found;
    }

    /**
     * Zostane po odstránení odkazov a e-mailov ešte nejaká prosba?
     */
    private function isBareLink(string $raw): bool
    {
        $rest = $this->joinSpacedDots($raw);
        $rest = preg_replace('~[\p{L}\d._%+-]+@[\p{L}\d.-]+\.[\p{L}]{2,}~u', ' ', $rest);
        $rest = preg_replace($this->urlPattern(), ' ', $rest);
        $rest = preg_replace('/[^\p{L}\d]+/u', '', $rest);

        return $rest !== $this->stripAll($raw) && mb_strlen($rest) <= 12;
    }

    /**
     * Text bez interpunkcie a emoji — na porovnanie, či sa vôbec niečo odstránilo.
     */
    private function stripAll(string $raw): string
    {
        return preg_replace('/[^\p{L}\d]+/u', '', $this->joinSpacedDots($raw));
    }

    /**
     * URL sú v DB uložené s medzerou za bodkou („www. hledamboha. cz"),
     * takže ich pred hľadaním treba zase zlepiť.
     */
    private function joinSpacedDots(string $s): string
    {
        return preg_replace('/(?<=[\p{L}\d])\s*\.\s*(?=[\p{L}\d])/u', '.', $s);
    }

    /**
     * `\b` počíta za slovo len ASCII, takže „ďakujem.skúsim" by sa cez `\bsk\b`
     * chytilo ako doména .sk. Preto sú okraje písané cez \p{L} lookaroundy.
     */
    private function urlPattern(): string
    {
        return '~https?://\S+'
            . '|(?<![\p{L}\d])www\.\S+'
            . '|(?<![\p{L}\d@.-])[\p{L}\d][\p{L}\d-]*(?:\.[\p{L}\d-]+)*\.(?:' . self::TLD . ')(?![\p{L}\d-])(?:/\S*)?~iu';
    }
}
