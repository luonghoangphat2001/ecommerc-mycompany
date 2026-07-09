<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\LoyaltyPoint;

class LoyaltyPointController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return LoyaltyPoint::class;
    }

    protected function title(): string
    {
        return 'Điểm Loyalty';
    }

    protected function routePrefix(): string
    {
        return 'admin.loyalty-points';
    }

    protected function fields(): array
    {
        return [
            'user_id' => ['label' => 'Khách hàng', 'type' => 'select', 'rules' => ['required', 'exists:users,id'], 'options' => \App\Models\User::pluck('name', 'id')->toArray()],
            'current_points' => ['label' => 'Điểm hiện tại', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'lifetime_points' => ['label' => 'Tổng điểm tích lũy', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
        ];
    }
}
