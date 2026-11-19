<?php

namespace App\Ecommerce\Dashboard\Services;

use App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Models\DepartmentCustomerReview;
use App\Models\DepartmentEmployeeContract;
use App\Models\DepartmentFinancialProposal;
use App\Models\DepartmentIncident;
use App\Models\DepartmentMaterialPrice;
use App\Models\DepartmentPurchaseOrder;
use App\Models\Media;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\TaxClass;
use App\Models\User;
use App\Models\Webhook;
use App\Support\Localization;

class DashboardService
{
    public function __construct(
        private readonly AnalyticsServiceInterface $analytics,
        private readonly Localization $localeContext
    ) {}

    public function getData(): array
    {
        $locale = $this->localeContext->current();
        $orderMetrics = $this->getOrderMetrics();
        $productMetrics = $this->getProductMetrics();

        return [
            'totalUsers' => User::query()->count(),
            'totalProducts' => $productMetrics['total_products'],
            'totalOrders' => $orderMetrics['total_orders'],
            'totalRevenue' => $orderMetrics['total_revenue'],
            'pendingOrders' => $orderMetrics['pending_orders'],
            'paidPayments' => Payment::query()->where('status', 'paid')->sum('amount'),
            'totalPosts' => Post::query()->count(),
            'totalPages' => Page::query()->count(),
            'totalMedia' => Media::query()->count(),
            'totalTaxClasses' => TaxClass::query()->count(),
            'totalWebhooks' => Webhook::query()->count(),
            'latestOrders' => Order::query()->latest('id')->take(6)->get(['id', 'number', 'status', 'total', 'currency', 'created_at']),
            'orderStatusCounts' => Order::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->orderBy('status')
                ->pluck('total', 'status'),
            'revenueSeries' => $this->analytics->getRevenueChartData('current'),
            'topProducts' => $this->analytics->getTopProductsChartData(8, $locale),
            'topProductsDonut' => $this->analytics->getTopProductsDonutChartData(5, $locale),
            'orderDistribution' => $this->analytics->getOrderStatusDistribution(),
            'swarmAgents' => DepartmentAgent::with('department')->get(),
            'pendingProposals' => DepartmentFinancialProposal::with(['department', 'user'])
                ->where('status', 'pending')
                ->orderByDesc('is_urgent')
                ->latest('id')
                ->take(5)
                ->get(),
            'urgentIncidents' => DepartmentIncident::with(['department', 'order'])
                ->whereIn('status', ['open', 'investigating'])
                ->latest('id')
                ->take(5)
                ->get(),
            'pendingPurchaseOrders' => DepartmentPurchaseOrder::with('department')
                ->whereIn('status', ['pending', 'shipping', 'partial'])
                ->latest('id')
                ->take(5)
                ->get(),
            'materialPrices' => DepartmentMaterialPrice::latest('id')->take(5)->get(),
            'expiringContractsCount' => DepartmentEmployeeContract::query()->where('end_date', '<=', now()->addDays(30))->count(),
            'negativeReviewsCount' => DepartmentCustomerReview::query()->where('rating', '<=', 2)->count(),
            'lowStockProductsCount' => $productMetrics['low_stock_products'],
            'urgentProposalsCount' => DepartmentFinancialProposal::query()->where('status', 'pending')->where('is_urgent', true)->count(),
            'departments' => Department::query()->where('is_active', true)->get(),
        ];
    }

    /**
     * Read the shared order totals in one aggregate query.
     *
     * @return array{total_orders: int, total_revenue: int|float, pending_orders: int}
     */
    private function getOrderMetrics(): array
    {
        $metrics = Order::query()
            ->selectRaw(
                'COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue, '
                . "SUM(CASE WHEN status IN ('pending', 'new', 'processing') THEN 1 ELSE 0 END) as pending_orders"
            )
            ->first();

        return [
            'total_orders' => (int) ($metrics->total_orders ?? 0),
            'total_revenue' => $metrics->total_revenue ?? 0,
            'pending_orders' => (int) ($metrics->pending_orders ?? 0),
        ];
    }

    /**
     * Read product totals and low-stock count in one aggregate query.
     *
     * @return array{total_products: int, low_stock_products: int}
     */
    private function getProductMetrics(): array
    {
        $metrics = Product::query()
            ->selectRaw('COUNT(*) as total_products, SUM(CASE WHEN qty <= 10 THEN 1 ELSE 0 END) as low_stock_products')
            ->first();

        return [
            'total_products' => (int) ($metrics->total_products ?? 0),
            'low_stock_products' => (int) ($metrics->low_stock_products ?? 0),
        ];
    }
}
