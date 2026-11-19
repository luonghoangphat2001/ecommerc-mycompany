<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Models\LoyaltyPoint;

class LoyaltyPointController extends BaseCrudController
{
    use HasAdminSelectOptions;

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

    protected function headerActions(): array
    {
        return [
            [
                'label' => __('admin.sidebar.loyalty_settings'),
                'url' => route('admin.settings.index', ['tab' => 'loyalty']),
                'class' => 'btn-secondary',
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'user_id' => ['label' => __('admin.loyalty.customer'), 'type' => 'select', 'rules' => ['required', 'exists:users,id'], 'options' => $this->userOptions()],
            'current_points' => ['label' => __('admin.loyalty.current_points'), 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
            'lifetime_points' => ['label' => __('admin.loyalty.lifetime_points'), 'type' => 'number', 'rules' => ['required', 'integer', 'min:0']],
        ];
    }
}
