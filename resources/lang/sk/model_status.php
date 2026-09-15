<?php

// Popisy App\Enums\ModelStatus. Kľúče sú hodnoty enumu.
return [
    'draft' => 'Koncept',
    'pending_review' => 'Čaká na schválenie',
    'rejected' => 'Zamietnutý',
    'scheduled' => 'Naplánovaný',
    'active' => 'Aktívny',
    'archived' => 'Archivovaný',
    'blocked' => 'Blokovaný',

    // Stav účtu používateľa (User::accountBadge()).
    'account_status' => 'Stav účtu',
    'status_reason' => 'Dôvod zmeny stavu',
    'status_reason_hint' => 'Pri neaktívnom stave je dôvod povinný a vidí ho iba administrácia.',
    'unverified' => 'Neoverený',
    'unverified_hint' => 'Používateľ ešte nepotvrdil e-mailovú adresu.',
    'verified_at' => 'E-mail overený :date',

    // Výber stavu v lište filtrov.
    'filter' => [
        'label' => 'Stav',
        'all' => 'Všetky stavy',
        'deleted' => 'Zrušené',
    ],
];
