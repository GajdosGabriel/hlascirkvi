<?php

namespace App\Notifications\Messages;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Spoločný základ všetkých e-mailov portálu.
 *
 * Každá notifikácia si predtým skladala oslovenie, podpis aj formátovanie
 * sama — a každá inak („Na kresťanskom portály HlasCirkvi.sk", anglické
 * „Thank you for using our application!", holý text správy uprostred vety).
 * Tu je oslovenie podľa vokatívu, jednotný podpis a pár stavebných blokov,
 * ktoré téma (resources/views/vendor/mail/html/themes/hlascirkvi.css) vie
 * pekne vykresliť.
 *
 * Bloky sú HtmlString v jednom riadku a bez prázdnych riadkov: celé telo
 * e-mailu ide cez Markdown a HTML blok končí prvým prázdnym riadkom.
 */
class PortalMail extends MailMessage
{
    public $salutation = 'S pozdravom';

    /**
     * Oslovenie podľa príjemcu. Vokatív dopĺňa UserObserver z tabuľky mien,
     * pri neznámom mene ostáva neutrálne „Dobrý deň,".
     */
    public static function for($notifiable = null): static
    {
        $vocative = $notifiable instanceof User ? trim((string) $notifiable->vocative) : '';

        return (new static)->greeting($vocative !== '' ? "Dobrý deň, {$vocative}," : 'Dobrý deň,');
    }

    /**
     * Text, ktorý napísal niekto iný (komentár, správa, modlitba). Escapuje
     * sa a stojí v odsadenom bloku, aby bolo jasné, čo píše portál a čo
     * cudzí človek.
     */
    public function quote(?string $text, ?string $caption = null): static
    {
        $text = trim(strip_tags((string) $text));

        if ($text === '') {
            return $this;
        }

        $body = str_replace(["\r\n", "\r", "\n"], '<br>', e(Str::limit($text, 1500)));
        $caption = $caption ? '<p class="quote-caption">'.e($caption).'</p>' : '';

        return $this->line(new HtmlString(
            '<table class="quote" width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr><td class="quote-cell">'
            .$caption.'<p class="quote-body">'.$body.'</p></td></tr></table>'
        ));
    }

    /**
     * Prehľad údajov v dvoch stĺpcoch — napr. kto sa registroval, kedy a ako.
     * Prázdne hodnoty sa vynechajú.
     *
     * @param  array<string, string|null>  $rows
     */
    public function details(array $rows): static
    {
        $html = '';

        foreach ($rows as $label => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $html .= '<tr><th class="details-label">'.e($label).'</th><td class="details-value">'.e($value).'</td></tr>';
        }

        if ($html === '') {
            return $this;
        }

        return $this->line(new HtmlString(
            '<table class="details" width="100%" cellpadding="0" cellspacing="0" role="presentation">'.$html.'</table>'
        ));
    }

    /**
     * Drobná poznámka pod hlavným obsahom — platnosť odkazu, „ak ste to
     * neboli vy" a podobne.
     */
    public function note(string $text): static
    {
        return $this->line(new HtmlString('<p class="note">'.e($text).'</p>'));
    }
}
