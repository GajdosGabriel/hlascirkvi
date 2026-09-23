<?php

namespace App\Console\Commands;

use App\Models\PendingRegistration;
use Illuminate\Console\Command;

/**
 * Jedna pripomienka registráciám, ktoré ani po
 * PendingRegistration::REMIND_AFTER_DAYS nikto nepotvrdil. Prvý e-mail
 * často skončí v nevyžiadanej pošte alebo zapadne; druhý už nie.
 */
class RemindPendingRegistrations extends Command
{
    protected $signature = 'registrations:remind';

    protected $description = 'Send a single reminder to unconfirmed registrations';

    public function handle(): int
    {
        $sent = 0;

        PendingRegistration::dueForReminder()->chunkById(100, function ($pending) use (&$sent) {
            foreach ($pending as $registration) {
                $registration->sendReminder();
                $sent++;
            }
        });

        $this->info(sprintf('Pripomienok odoslaných: %d', $sent));

        return self::SUCCESS;
    }
}
