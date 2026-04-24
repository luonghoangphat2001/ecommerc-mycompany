<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EloquentAnalyticsRepository implements AnalyticsRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getMonthlyRevenue(string $filter): array
    {
        $monthExpr = $this->getMonthExpression();

        $query = DB::table('shop_orders')
            ->selectRaw("{$monthExpr} as month, SUM(total) as total")
            ->groupBy('month')
            ->orderBy('month');

        if ($filter === 'last_year') {
            $query->whereYear('created_at', Carbon::now()->subYear()->year);
        } elseif ($filter !== 'all_time') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        return $query->pluck('total', 'month')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getMonthlyCustomerRegistrations(string $filter): array
    {
        $monthExpr = $this->getMonthExpression();

        $query = DB::table('users')
            ->selectRaw("{$monthExpr} as month, COUNT(id) as total")
            ->groupBy('month')
            ->orderBy('month');

        if ($filter === 'last_year') {
            $query->whereYear('created_at', Carbon::now()->subYear()->year);
        } elseif ($filter !== 'all_time') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        return $query->pluck('total', 'month')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getMonthlyOrderStatuses(int $year): array
    {
        $monthExpr = $this->getMonthExpression();

        return DB::table('shop_orders')
            ->select(
                DB::raw("{$monthExpr} as month"),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', $year)
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getStatsSummary(?array $filters = []): array
    {
        $filters = $filters ?? [];
        $startDate = !is_null($filters['startDate'] ?? null) ? Carbon::parse($filters['startDate']) : now()->subDays(7);
        $endDate = !is_null($filters['endDate'] ?? null) ? Carbon::parse($filters['endDate']) : now();

        $customerData = DB::table('users')->whereBetween('created_at', [$startDate, $endDate])->count();
        $totalRevenue = DB::table('shop_orders')->whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $totalOrders = DB::table('shop_orders')->whereBetween('created_at', [$startDate, $endDate])->count();
        $pendingOrders = DB::table('shop_orders')->where('status', 'processing')->count();

        return [
            'totalSales' => DB::table('shop_orders')->sum('total'), // Global total
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'newCustomers' => $customerData,
            'pendingOrders' => $pendingOrders,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTopProducts(int $limit = 6, string $locale = 'en'): array
    {
        $nameField = $this->getJsonExtractExpression('shop_products.name', $locale);
        
        return DB::table('shop_products')
            ->select(
                'shop_products.id',
                DB::raw("{$nameField} as title"),
                'shop_products.product_images',
                DB::raw('SUM(shop_order_items.unit_price) as total_revenue'),
                DB::raw('COUNT(shop_order_items.id) as total_orders')
            )
            ->join('shop_order_items', 'shop_products.id', '=', 'shop_order_items.shop_product_id')
            ->groupBy('shop_products.id', 'shop_products.name', 'shop_products.product_images')
            ->orderByDesc('total_orders')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $media = DB::table('media')->where('id', $product->product_images)->first();
                $product->image_url = $media ? $media->path : asset('images/default.jpg');
                return (array) $product;
            })
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getTopCustomers(int $limit = 10): array
    {
        return DB::table('shop_orders')
            ->select(
                'shop_orders.user_id',
                'shop_order_addresses.email',
                DB::raw("CONCAT(COALESCE(shop_order_addresses.first_name, ''), ' ', COALESCE(shop_order_addresses.last_name, '')) as customer_name"),
                DB::raw('SUM(shop_orders.total) as total_spent'),
                'users.photo'
            )
            ->leftJoin('users', 'shop_orders.user_id', '=', 'users.id')
            ->leftJoin('shop_order_addresses', function ($join) {
                $join->on('shop_orders.id', '=', 'shop_order_addresses.addressable_id')
                    ->where('shop_order_addresses.addressable_type', '=', 'App\Models\Order')
                    ->where('shop_order_addresses.type', '=', 'shipping');
            })
            ->groupBy('shop_orders.user_id', 'shop_order_addresses.email', 'shop_order_addresses.first_name', 'shop_order_addresses.last_name', 'users.photo')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return (array) [
                    'id' => $order->user_id,
                    'name' => $order->user_id ? ($order->customer_name ?: 'Member') : ($order->customer_name ?: 'Guest'),
                    'email' => $order->email,
                    'photo' => $order->photo,
                    'total_spent' => $order->total_spent,
                    'is_guest' => $order->user_id === null
                ];
            })
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getRecentCustomers(int $limit = 10): array
    {
        return DB::table('users')
            ->latest()
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusDistribution(): array
    {
        return DB::table('shop_orders')
            ->select('status', DB::raw('count(*) as total'))
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getFilteredNewCustomersQuery(?string $from = null, ?string $until = null): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $query = $this->getNewCustomersQuery();

        if ($from && $until) {
            $query->whereBetween('shop_orders.created_at', [$from, $until]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function applyNewCustomersFilter($query, ?string $from = null, ?string $until = null)
    {
        if ($from && $until) {
            $query->whereBetween('shop_orders.created_at', [$from, $until]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getNewCustomersQuery(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Order::query()
            ->with('user')
            ->leftJoin('shop_order_addresses', function ($join) {
                $join->on('shop_orders.id', '=', 'shop_order_addresses.addressable_id')
                    ->where('shop_order_addresses.addressable_type', '=', 'App\Models\Order')
                    ->where('shop_order_addresses.type', '=', 'shipping');
            })
            ->selectRaw("MIN(shop_orders.id) as id, shop_order_addresses.email, shop_orders.user_id, CONCAT(COALESCE(shop_order_addresses.first_name, ''), ' ', COALESCE(shop_order_addresses.last_name, '')) as customer_name, SUM(shop_orders.total) as total_spent, MAX(shop_orders.created_at) as latest_order")
            ->groupBy('shop_order_addresses.email', 'shop_orders.user_id', 'shop_order_addresses.first_name', 'shop_order_addresses.last_name');
    }

    /**
     * @inheritDoc
     */
    public function getTopProductsChartData(int $limit = 10, string $locale = 'en'): array
    {
        $nameField = $this->getJsonExtractExpression('shop_products.name', $locale);

        return DB::table('shop_orders')
            ->join('shop_order_items', 'shop_order_items.order_id', '=', 'shop_orders.id')
            ->join('shop_products', 'shop_order_items.shop_product_id', '=', 'shop_products.id')
            ->selectRaw("shop_order_items.shop_product_id, {$nameField} as product_name, SUM(shop_orders.total) as revenue")
            ->whereYear('shop_orders.created_at', date('Y'))
            ->groupBy('shop_order_items.shop_product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getTopProductsDonutChartData(int $limit = 5, string $locale = 'en'): array
    {
        $nameField = $this->getJsonExtractExpression('shop_products.name', $locale);

        return DB::table('shop_order_items')
            ->join('shop_orders', 'shop_order_items.order_id', '=', 'shop_orders.id')
            ->join('shop_products', 'shop_order_items.shop_product_id', '=', 'shop_products.id')
            ->selectRaw("shop_products.id, {$nameField} as product_name, SUM(shop_order_items.qty) as total_sold")
            ->whereYear('shop_orders.created_at', Carbon::now()->year)
            ->groupBy('shop_products.id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getMonthlyCustomerStats(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentCount = DB::table('users')->where('created_at', '>=', $currentMonth)->count();
        $previousCount = DB::table('users')->whereBetween('created_at', [$previousMonth, $currentMonth])->count();

        $percentageChange = $previousCount > 0
            ? (($currentCount - $previousCount) / $previousCount) * 100
            : 0;

        return [
            'current_count' => $currentCount,
            'previous_count' => $previousCount,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    /**
     * Get database specific month expression.
     *
     * @return string
     */
    protected function getMonthExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? 'CAST(strftime("%m", created_at) AS INTEGER)'
            : 'MONTH(created_at)';
    }

    /**
     * Get database specific JSON extract expression.
     *
     * @param string $column
     * @param string $locale
     * @return string
     */
    protected function getJsonExtractExpression(string $column, string $locale): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "json_extract({$column}, '$.{$locale}')"
            : "JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.\"{$locale}\"'))";
    }
}
