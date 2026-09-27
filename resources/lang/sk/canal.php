<?php

return [
    'actions' => [
        'label' => 'Možnosti kanála',
        'show' => 'Zobraziť',
        'edit' => 'Upraviť',
        'dashboard' => 'Nástenka kanála',
        'remove' => 'Vyradiť zo zoznamu',
        'remove_hint' => 'Vyradiť zo zoznamu — kanál samotný ostáva',
        'remove_confirmation' => 'Vyradiť kanál :title z predného zoznamu?',
    ],
    'identity_mode' => [
        'label' => 'Režim identity',
        'all' => 'Všetky identity',
        'options' => [
            'organization' => 'Organizácia',
            'personal' => 'Osobný',
            'pseudonymous' => 'Pseudonymný',
        ],
    ],
    'filter' => [
        'label' => 'Stav kanála',
        'options' => [
            '' => 'Všetky nezrušené',
            'published' => 'Publikované',
            'unpublished' => 'Nepublikované',
            'deletedAt' => 'Zrušené',
        ],
    ],
];
