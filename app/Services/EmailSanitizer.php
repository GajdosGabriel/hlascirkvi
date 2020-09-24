<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 25/06/2018
 * Time: 14:18
 */

namespace App\Services;


class EmailSanitizer
{
    protected $typoDictionary = [
        '@seznam.cz' => [
            '@aeznam.cz',
            '@deznam.cz',
            '@eznam.cz',
            '@sdznam.cz',
            '@seeznam.cz',
            '@senam.c',
            '@senam.cz',
            '@senzam.cz',
            '@sesnam.cz',
            '@sexnam.cz',
            '@sezanm.cz',
            '@sezbam.cz',
            '@sezmam.cz',
            '@sezn.cz',
            '@sezna.cz',
            '@seznaam.cz',
            '@seznam.',
            '@seznam..cz',
            '@seznam.c',
            '@seznam.ccz',
            '@seznam.cs',
            '@seznam.cx',
            '@seznam.czz',
            '@seznam.vz',
            '@seznam.xz',
            '@seznam.z',
            '@sezman.cz',
            '@seznamc.z',
            '@seznamm.cz',
            '@seznan.cz',
            '@seznm.cz',
            '@seznma.cz',
            '@seznnam.cz',
            '@seznqm.cz',
            '@seznsm.cz',
            '@seznzm.cz',
            '@sezznam.cz',
            '@srznam.cz',
            '@sseznam.cz',
            '@sznam.cz',
        ],
        '@email.cz' => [
            '@dmail.cz',
            '@eail.cz',
            '@eamil.cz',
            '@eemail.cz',
            '@emai.cz',
            '@emaik.cz',
            '@email.c',
            '@email.vz',
            '@email.xz',
            '@emaip.cz',
            '@emaul.cz',
            '@emial.cz',
            '@emil.cz',
            '@emsil.cz',
            '@rmail.cz',
        ],
        '@centrum.cz' => [
            '@ccentrum.cz',
            '@cebtrum.cz',
            '@ceentrum.cz',
            '@cemtrum.cz',
            '@cengrum.cz',
            '@cenntrum.cz',
            '@cenrrum.cz',
            '@cenrtum.cz',
            '@cenrum.cz',
            '@centeum.cz',
            '@centrm.cz',
            '@centru.cz',
            '@centrum..cz',
            '@centrum.c',
            '@centrum.ca',
            '@centrum.vz',
            '@centrumm.cz',
            '@centrun.cz',
            '@centtum.cz',
            '@centum.cz',
            '@centurm.cz',
            '@cetnrum.cz',
            '@cetrum.cz',
            '@cntrum.cz',
            '@entrum.cz',
            '@ventrum.cz',
        ],
        '@post.cz' => [
            '@post.z',
            '@psot.cz',
        ],
        '@gmail.com' => [
            '@fmail.com',
            '@gail.com',
            '@gamil.com',
            '@ggmail.com',
            '@gmai.com',
            '@gmaiil.com',
            '@gmaik.com',
            '@gmail.',
            '@gmail.c',
            '@gmail.cim',
            '@gmail.cm',
            '@gmail.co',
            '@gmail.comm',
            '@gmail.con',
            '@gmail.cpm',
            '@gmail.ocm',
            '@gmail.om',
            '@gmail.vom',
            '@gmail.vz',
            '@gmail.xom',
            '@gmaill.com',
            '@gmaio.com',
            '@gmal.com',
            '@gmali.com',
            '@gmaol.com',
            '@gmaul.com',
            '@gmial.com',
            '@gmIl.com',
            '@gmmail.com',
            '@gnail.com',
            '@vmail.com',
        ],
        '@hotmail.com' => [
            '@hitmail.com',
            '@hmail.com',
            '@hotmail.con',
            '@hotmail.vom',
            '@hotmailc.om',
            '@hotmsil.com',
            '@hotnail.com',
        ],
        '@yahoo.com' => [
            '@yaho.com',
            '@yahoi.com',
            '@yahoo.cim',
            '@yahoo.coom',
            '@ymail.com',
        ],
    ];

    protected $convertTable = [
        '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae','Ç' => 'c',
        'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß' => 'ss','à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'ae','ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
        'ā' => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
        'ć' => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
        'č' => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
        'ē' => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
        'ę' => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
        'ğ' => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
        'ĥ' => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
        'ī' => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
        'ı' => 'i', 'Ĳ' => 'ij','ĳ' => 'ij','Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
        'ķ' => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
        'Ľ' => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
        'Ń' => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
        'ŉ' => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
        'ŏ' => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe','œ' => 'oe','Ŕ' => 'r',
        'ŕ' => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
        'ś' => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
        'š' => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
        'ŧ' => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
        'ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
        'ų' => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
        'Ź' => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
        'ſ' => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
        'ư' => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
        'ǒ' => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
        'ǘ' => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
        'ǻ' => 'a', 'Ǽ' => 'ae','ǽ' => 'ae','Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
        'Ё' => 'jo','Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
        'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh','З' => 'z',
        'И' => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
        'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch','Ш' => 'sh','Щ' => 'sch',
        'Ъ' => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je','Ю' => 'ju','Я' => 'ja',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ж' => 'zh','з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh','щ' => 'sch','ъ' => '-','ы' => 'y', 'ь' => '-', 'э' => 'je',
        'ю' => 'ju','я' => 'ja','ё' => 'jo','є' => 'e', 'і' => 'i', 'ї' => 'i',
        'Ґ' => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
        'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
        'ך' => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
        'נ' => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
        'צ' => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
    ];

    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function getSanitized()
    {
        $email = trim($this->email);
        $email = mb_strtolower($email);
        $email = str_replace(' ', '', $email);
        $email = strtr($email, $this->convertTable);
        $email= $this->fixEmailTypos($email);

        return $email;
    }

    protected function fixEmailTypos($email)
    {
        $fixedEmail = $email;
        $position = strpos($fixedEmail, '@');
        $domain = substr($fixedEmail, $position);

        foreach ($this->typoDictionary as $replace => $typos) {
            if (in_array($domain, $typos)) {
                $fixedEmail = str_replace($domain, $replace, $fixedEmail);
                break;
            }
        }


        return $fixedEmail;
    }
}