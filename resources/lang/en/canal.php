<?php

return [
    'actions' => [
        'label' => 'Channel options',
        'show' => 'View',
        'edit' => 'Edit',
        'dashboard' => 'Channel dashboard',
        'remove' => 'Remove from list',
        'remove_hint' => 'Remove from the list — the channel itself is kept',
        'remove_confirmation' => 'Remove channel :title from the front list?',
    ],
    'identity_mode' => [
        'label' => 'Identity mode',
        'all' => 'All identities',
        'options' => [
            'organization' => 'Organization',
            'personal' => 'Personal',
            'pseudonymous' => 'Pseudonymous',
        ],
    ],
    'filter' => [
        'label' => 'Channel status',
        'options' => [
            '' => 'All non-deleted',
            'published' => 'Published',
            'unpublished' => 'Unpublished',
            'deletedAt' => 'Deleted',
        ],
    ],
];
