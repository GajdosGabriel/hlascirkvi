<?php

namespace App\Notifications\Admin;

use App\Notifications\Messages\PortalMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Súhrn chýb, ktoré pribudli v laravel.log a dajú sa opraviť v systéme.
 * Posiela App\Console\Commands\LogErrorsReport z plánovača.
 */
class LogErrorsFound extends Notification
{
    use Queueable;

    /** Viac druhov v jednom maile už nikto nečíta. */
    private const SHOWN = 20;

    /**
     * @param  list<array{message: string, class: ?string, location: ?string, count: int, first: string, last: string}>  $groups
     */
    public function __construct(
        public array $groups,
    ) {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $total = array_sum(array_column($this->groups, 'count'));

        $mail = PortalMail::for($notifiable)
            ->subject('Chyby v logu portálu: ' . count($this->groups) . ' druh(y), spolu ' . $total . '×')
            ->line('v `storage/logs/laravel.log` pribudli chyby, ktoré si pravdepodobne žiadajú opravu v kóde, databáze alebo konfigurácii.');

        foreach (array_slice($this->groups, 0, self::SHOWN) as $group) {
            // Popisok citátu je kapitálkami — cesta k súboru preto ide do tela.
            $where = implode(' · ', array_filter([$group['class'], $group['location']]));

            $mail->quote(
                $group['message'] . ($where !== '' ? "\n" . $where : ''),
                $group['count'] . '× · naposledy ' . $group['last'],
                500,
            );
        }

        if (count($this->groups) > self::SHOWN) {
            $mail->line('… a ďalších ' . (count($this->groups) - self::SHOWN) . ' druhov chýb.');
        }

        return $mail->note('Celé záznamy so stack trace sú v laravel.log na serveri. Šum z výpadkov cudzích služieb sa vynecháva (config/logging.php → report.ignore).');
    }
}
