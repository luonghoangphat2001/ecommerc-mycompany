<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Workspace\Services\CskhService;
use App\Enums\CustomerReviewSentiment;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasPermissionedServiceCrudActions;
use App\Models\Coupon;
use App\Models\DepartmentCustomerReview;

class DepartmentCustomerReviewController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasPermissionedServiceCrudActions;

    public function __construct(private readonly CskhService $cskhService)
    {
        parent::__construct();
    }

    protected function modelClass(): string
    {
        return DepartmentCustomerReview::class;
    }

    protected function title(): string
    {
        return 'Đánh giá Khách hàng';
    }

    protected function routePrefix(): string
    {
        return 'admin.customer-reviews';
    }

    protected function searchable(): array
    {
        return ['customer_name', 'content'];
    }

    protected function fields(): array
    {
        return [
            'department_id' => [
                'label' => 'Phòng ban',
                'type' => 'select',
                'rules' => ['required', 'exists:departments,id'],
                'options' => $this->departmentOptions(),
            ],
            'user_id' => [
                'label' => 'Tài khoản KH (Nếu có)',
                'type' => 'select',
                'rules' => ['nullable', 'exists:users,id'],
                'options' => ['' => '-- Trống --'] + $this->userOptions(),
            ],
            'customer_name' => [
                'label' => 'Tên khách hàng',
                'rules' => ['nullable', 'string', 'max:255'],
            ],
            'content' => [
                'label' => 'Nội dung đánh giá',
                'type' => 'textarea',
                'rules' => ['required', 'string'],
            ],
            'rating' => [
                'label' => 'Đánh giá (1-5 sao)',
                'type' => 'number',
                'rules' => ['required', 'integer', 'min:1', 'max:5'],
            ],
            'sentiment' => [
                'label' => 'Thái độ',
                'type' => 'select',
                'rules' => ['required', 'in:positive,neutral,negative'],
                'options' => CustomerReviewSentiment::options(),
            ],
            'coupon_id' => [
                'label' => 'Mã đền bù (Coupon)',
                'type' => 'select',
                'rules' => ['nullable', 'exists:shop_coupons,id'],
                'options' => ['' => '-- Trống --'] + Coupon::orderBy('code')->pluck('code', 'id')->toArray(),
            ],
            'reply_content' => [
                'label' => 'Nội dung phản hồi',
                'type' => 'textarea',
                'rules' => ['nullable', 'string'],
            ],
        ];
    }

    protected function crudService(): object
    {
        return $this->cskhService;
    }

    protected function createServiceMethod(): string
    {
        return 'createReview';
    }

    protected function updateServiceMethod(): string
    {
        return 'updateReview';
    }

    protected function deleteServiceMethod(): string
    {
        return 'deleteReview';
    }
}
