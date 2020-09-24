<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 28. 9. 2019
 * Time: 16:55
 */

namespace App\Inspect;


class CleanBodyText
{
    protected $post;

    protected $invalitWords =
        [
            // Martin dom
        'Web: http://martindom.sk/',
        'FB: https://www.facebook.com/spolocenstvomartindom/',
        'https://www.facebook.com/martindomworship/',
        'Follow us:',
        'SoundCloud: https://soundcloud.com/SpolocenstvoMartindom',
        'Instagram: https://www.instagram.com/martindomsk/',

        // Spoločenstvo dobrého pastiera
        'PRIHLÁSTE SA, PROSÍME, NA ODBER NÁŠHO YOUTUBE KANÁLU, kliknutím na červené tlačidlo s názvom ODOBERAŤ a pokiaľ chcete byť mailom  notifikovaní o každom našom novom videu, tak je potrebné po zakliknutí tlačidla odoberať zakliknúť hneď vedľa tohto tlačidla aj ikonku zvončeku. Iba ak zakliknete aj zvonček (zvonček bude v zátvorkách), tak iba vtedy budete youtubom notifikovaní o každom našom novom videu.',
        'http://dobrypastier.sk/',

        //  Milost Banská Bystrica
        'zdroj zvuku: audiojungle.com',
        'www.milost.sk/live',
        'http://povazskabystrica.casd.sk',

        //CB Prešov
        'Web: http://www.cbpo.sk',
        'Sledujte nás aj na Facebooku: https://www.facebook.com/CirkevBratskaVPresove',

         // Michal Zamkovský
         'http://www.slovo.sk'
    ];

    public function __construct($post)
    {
        $this->post = $post;
    }

    public function handle()
    {
        $this->post->update( ['body' => str_replace($this->invalitWords ,'', $this->post->body)] );
    }


}