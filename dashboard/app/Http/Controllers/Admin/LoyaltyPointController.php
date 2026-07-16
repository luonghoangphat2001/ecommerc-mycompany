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
        return __('admin.loyalty.label');
    }

    protected function routePrefix(): string
    {
        return 'admin.loyalty-points';
    }

    protected function fields(): array
    {
        return [
            'user_id' => ['label' => __('admin.loyalty.customer'), 'type' => 'select', 'rules' => ['required', 'exists:users,id'], 'options' => \App\Models\User::pluck('name', 'id')->toArray()],
            'current_points' => ['label' => __('admin.loyalty.current_points'), 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'lifetime_points' => ['label' => __('admin.loyalty.lifetime_points'), 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
        ];
    }
}
