<?php

return [
    'price_mismatch' => 'Product price has changed. Please refresh your cart.',
    'insufficient_stock' => 'Insufficient stock for one or more products in the order.',
    'concurrency_collision' => 'Stock was updated by another request. Please try again.',

    /* API Success & Error Messages */
    'api' => [
        'label' => 'REST API',
        'enabled' => 'Enable API',
        'settings' => 'API Settings',
        'documentation' => 'API Documentation',
        'view_documentation' => 'View REST API Documentation (Scramble)',
        'idempotency' => 'Idempotency',
        'idempotency_ttl' => 'Idempotency Key TTL (seconds)',
        'endpoints' => 'API Endpoints',
        'documentation_desc' => 'View full API reference and integration guide.',
        'orders_retrieved' => 'Orders retrieved successfully.',
        'order_placed' => 'Order placed successfully.',
        'order_not_found' => 'Order not found.',
        'product_not_found' => 'Product not found.',
        'combo_not_found' => 'Combo product not found.',
        'success' => 'Success',
        'error' => 'Error',
        'created' => 'Created successfully',
        'bad_request' => 'Bad Request',
        'unauthorized' => 'Unauthorized',
        'forbidden' => 'Forbidden',
        'not_found' => 'Resource not found',
        'validation_error' => 'Data validation error',
    ],

    /* System Logs Messaging */
    'logs' => [
        'label' => 'System Logs',
        'select_file' => 'Select Log File',
        'empty_state' => 'Select a log file from the dropdown to view its content.',
        'file_not_found' => 'Log file not found.',
    ],

    /* Operational Actions */
    'cleanup' => [
        'webhook_logs_success' => 'Successfully deleted :count webhook logs older than :days days.',
    ],
    'export' => [
        'completed' => 'Export completed: :rows rows exported.',
        'failed' => ':rows rows failed.',
    ],
    'import' => [
        'completed' => 'Import completed: :rows rows imported.',
        'failed' => ':rows rows failed.',
    ],
];
