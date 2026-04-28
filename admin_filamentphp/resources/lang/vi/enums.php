<?php

return [
    /* Order & General Statuses */
    'pending_payment' => 'Chờ thanh toán',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đã giao',
    'delivered' => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
    'refunded' => 'Đã hoàn tiền',
    'new' => 'Mới',
    'open' => 'Mở',

    /* Webhook Specific Statuses */
    'webhooks' => [
        'statuses' => [
            'delivered' => 'Đã gửi',
            'failed' => 'Thất bại',
            'pending' => 'Đang chờ',
        ],
    ],

    /* SMTP Config Constants */
    'smtp_host' => 'Máy chủ SMTP',
    'smtp_port' => 'Cổng SMTP',
    'smtp_username' => 'Tên người dùng SMTP',
    'smtp_password' => 'Mật khẩu SMTP',
];
