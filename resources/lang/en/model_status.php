<?php

// Labels for App\Enums\ModelStatus. Keys are the enum values.
return [
    'draft' => 'Draft',
    'pending_review' => 'Pending review',
    'rejected' => 'Rejected',
    'scheduled' => 'Scheduled',
    'active' => 'Active',
    'archived' => 'Archived',
    'blocked' => 'Blocked',

    // User account state (User::accountBadge()).
    'account_status' => 'Account status',
    'status_reason' => 'Reason for status change',
    'status_reason_hint' => 'A reason is required for an inactive status and is visible only to administrators.',
    'unverified' => 'Unverified',
    'unverified_hint' => 'The user has not confirmed their email address yet.',
    'verified_at' => 'Email verified :date',

    'filter' => [
        'label' => 'Status',
        'all' => 'All statuses',
        'deleted' => 'Deleted',
    ],
];
