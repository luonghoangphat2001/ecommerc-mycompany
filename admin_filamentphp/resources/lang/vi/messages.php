<?php

return [
    /* API Success & Error Messages */
    'api' => [
        'label' => 'REST API',
        'enabled' => 'Bật API',
        'settings' => 'Cài đặt API',
        'documentation' => 'Tài liệu API',
        'view_documentation' => 'Xem tài liệu REST API (Scramble)',
        'endpoints' => 'Cổng kết nối (Endpoints)',
        'idempotency' => 'Chống trùng lặp',
        'idempotency_ttl' => 'Thời gian lưu khóa Idempotency (giây)',
        'documentation_desc' => 'Xem hướng dẫn chi tiết và tài liệu tích hợp API.',
        'orders_retrieved' => 'Lấy danh sách đơn hàng thành công.',
        'order_placed' => 'Đặt hàng thành công.',
        'order_not_found' => 'Không tìm thấy đơn hàng.',
        'success' => 'Thành công',
        'error' => 'Lỗi',
        'created' => 'Tạo thành công',
        'bad_request' => 'Yêu cầu không hợp lệ',
        'unauthorized' => 'Không được phép truy cập',
        'forbidden' => 'Bị cấm truy cập',
        'not_found' => 'Không tìm thấy tài nguyên',
        'validation_error' => 'Lỗi xác thực dữ liệu',
        'price_mismatch' => 'Giá sản phẩm đã thay đổi. Vui lòng kiểm tra lại giỏ hàng.',
    ],

    /* System Logs Messaging */
    'logs' => [
        'label' => 'Nhật ký hệ thống',
        'select_file' => 'Chọn file log',
        'empty_state' => 'Chọn một file log từ danh sách để xem nội dung.',
        'file_not_found' => 'Không tìm thấy file log.',
    ],

    /* Operational Actions */
    'cleanup' => [
        'webhook_logs_success' => 'Đã xóa thành công :count bản ghi webhook cũ hơn :days ngày.',
    ],
    'export' => [
        'completed' => 'Xuất hoàn tất: :rows dòng đã xuất.',
        'failed' => ':rows dòng thất bại.',
    ],
    'import' => [
        'completed' => 'Nhập hoàn tất: :rows dòng đã nhập.',
        'failed' => ':rows dòng thất bại.',
    ],
    /* Coupon Messages */
    'coupon_expired' => 'Mã giảm giá đã hết hạn.',
    'coupon_usage_limit_reached' => 'Mã giảm giá này đã đạt giới hạn lượt sử dụng.',
    'coupon_min_amount_not_met' => 'Số tiền đơn hàng chưa đạt mức tối thiểu để áp dụng mã này.',
    'coupon_restriction_not_met' => 'Đơn hàng không đáp ứng điều kiện áp dụng mã giảm giá này.',
    'coupon_not_found' => 'Mã giảm giá không hợp lệ hoặc không tồn tại.',
    'coupon_applied_success' => 'Áp dụng mã giảm giá thành công.',
];
