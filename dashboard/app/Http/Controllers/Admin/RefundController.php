<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\OrderRefund;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return OrderRefund::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.refunds';
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

    public function index(Request $request): View
    {
        $query = OrderRefund::with(['order.user']);

        // Search
        $this->applySearch($query, $request);

        $items = $this->paginateIndex($query);

        return view('admin.refunds.index', [
            'title' => __($this->title()),
            'items' => $items,
            'routePrefix' => $this->routePrefix(),
            'canCreate' => false,
            'canEdit' => false,
            'canDelete' => false,
            'canImportExport' => false,
            'headerActions' => [],
        ]);
    }
}
