<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\OrderRefund;

class RefundController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return OrderRefund::class;
    }

    protected function title(): string
    {
        return 'Refunds';
    }

    protected function routePrefix(): string
    {
        return 'admin.refunds';
    }

    protected function searchable(): array
    {
        return ['reason'];
    }

    protected function fields(): array
    {
        return [
            'order_id' => ['label' => 'Order ID', 'type' => 'number', 'rules' => ['required', 'integer']],
            'amount' => ['label' => 'Amount', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'reason' => ['label' => 'Reason', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        ];
    }
}
