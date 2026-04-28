<?php

return [
    /* Order & General Statuses */
    'pending_payment' => 'Pending Payment',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Completed',
    'cancelled' => 'Cancelled',
    'refunded' => 'Refunded',
    'new' => 'New',
    'open' => 'Open',

    /* Webhook Specific Statuses */
    'webhooks' => [
        'statuses' => [
            'delivered' => 'Delivered',
            'failed' => 'Failed',
            'pending' => 'Pending',
        ],
    ],

    /* SMTP Config Constants */
    'smtp_host' => 'SMTP Host',
    'smtp_port' => 'SMTP Port',
    'smtp_username' => 'SMTP Username',
    'smtp_password' => 'SMTP Password',
];
