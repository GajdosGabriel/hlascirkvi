<?php

return [
    'type' => [
        'label' => 'Channel type',
        'all' => 'All types',
        'options' => [
            'organization' => 'Organization',
            'personal' => 'Personal',
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