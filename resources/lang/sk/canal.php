<?php

return [
    'type' => [
        'label' => 'Typ kanála',
        'all' => 'Všetky typy',
        'options' => [
            'organization' => 'Organizácia',
            'personal' => 'Osobný',
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