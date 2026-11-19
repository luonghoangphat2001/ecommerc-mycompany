<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->hasRole('super_admin')) {
                return $next($request);
            }

            try {
                if (! auth()->user()->hasPermissionTo('view_dashboard')) {
                    abort(403, 'Unauthorized action. Missing permission: view_dashboard');
                }
            } catch (\Exception $e) {
                abort(403, 'Unauthorized action. Missing permission: view_dashboard');
            }

            return $next($request);
        });
    }

    public function __invoke(): View
    {
        return view('admin.dashboard', array_merge([
            'title' => __('admin.dashboard.title'),
        ], $this->dashboardService->getData()));
    }
}
