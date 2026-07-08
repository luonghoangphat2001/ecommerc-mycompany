<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Payment;

class PaymentController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Payment::class;
    }

    protected function title(): string
    {
        return 'Payments';
    }

    protected function routePrefix(): string
    {
        return 'admin.payments';
    }

    protected function searchable(): array
    {
        return ['method', 'status', 'provider', 'reference'];
    }

    protected function fields(): array
    {
        return [
            'order_id' => ['label' => 'Order ID', 'type' => 'number', 'rules' => ['required', 'integer']],
            'method' => ['label' => 'Method', 'rules' => ['required', 'string', 'max:50']],
            'currency' => ['label' => 'Currency', 'rules' => ['required', 'string', 'max:10']],
            'amount' => ['label' => 'Amount', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'status' => ['label' => 'Status', 'rules' => ['required', 'string', 'max:50']],
            'provider' => ['label' => 'Provider', 'rules' => ['nullable', 'string', 'max:100']],
            'reference' => ['label' => 'Reference', 'rules' => ['nullable', 'string', 'max:255']],
        ];
    }
}
