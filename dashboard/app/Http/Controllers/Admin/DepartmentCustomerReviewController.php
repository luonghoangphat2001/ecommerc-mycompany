<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentCustomerReview;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Http\Request;

class DepartmentCustomerReviewController extends BaseCrudController
{
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
                'options' => \App\Models\Department::pluck('name', 'id')->toArray(),
            ],
            'user_id' => [
                'label' => 'Tài khoản KH (Nếu có)',
                'type' => 'select',
                'rules' => ['nullable', 'exists:users,id'],
                'options' => ['' => '-- Trống --'] + User::orderBy('name')->pluck('name', 'id')->toArray(),
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
                'options' => [
                    'positive' => 'Tích cực',
                    'neutral' => 'Bình thường',
                    'negative' => 'Tiêu cực',
                ],
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

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('create')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $cskhService = app(\App\Ecommerce\Workspace\Services\CskhService::class);
        $cskhService->createReview($validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Tạo mới thành công.');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('update')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);
        
        $cskhService = app(\App\Ecommerce\Workspace\Services\CskhService::class);
        $cskhService->updateReview($id, $validated);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Cập nhật thành công.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        if (!$this->hasPermission('delete')) {
            abort(403);
        }

        $cskhService = app(\App\Ecommerce\Workspace\Services\CskhService::class);
        $cskhService->deleteReview($id);

        return redirect()->route($this->routePrefix() . '.index')->with('success', 'Xóa thành công.');
    }
}
