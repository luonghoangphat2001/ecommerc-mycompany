<?php

namespace App\Contracts\Services;

interface AnalyticsServiceInterface
{
    /**
     * Get revenue chart data.
     *
     * @param string $filter
     * @return array
     */
    public function getRevenueChartData(string $filter): array;

    /**
     * Get customer registrations chart data.
     *
     * @param string $filter
     * @return array
     */
    public function getCustomerChartData(string $filter): array;

    /**
     * Get order status chart data.
     *
     * @param int $year
     * @return array
     */
    public function getOrderStatusChartData(int $year): array;

    /**
     * Get statistics summary for dashboard.
     *
     * @param array|null $filters
     * @return array
     */
    public function getStatsSummary(?array $filters = []): array;

    /**
     * Get top products for dashboard.
     *
     * @param int $limit
     * @param string $locale
     * @return array
     */
    public function getTopProducts(int $limit = 6, string $locale = 'en'): array;

    /**
     * Get top customers for dashboard.
     *
     * @param int $limit
     * @return array
     */
    public function getTopCustomers(int $limit = 10): array;

    /**
     * Get recent customers table data.
     *
     * @param int $limit
     * @return array
     */
    public function getRecentCustomers(int $limit = 10): array;

    /**
     * Get order status distribution chart data.
     *
     * @return array
     */
    public function getOrderStatusDistribution(): array;

    /**
     * Get top products chart data.
     *
     * @param int $limit
     * @param string $locale
     * @return array
     */
    public function getTopProductsChartData(int $limit = 10, string $locale = 'en'): array;

    /**
     * Get top products donut chart data.
     *
     * @param int $limit
     * @param string $locale
     * @return array
     */
    public function getTopProductsDonutChartData(int $limit = 5, string $locale = 'en'): array;

    /**
     * Get monthly customer statistics (current vs previous).
     *
     * @return array
     */
    public function getMonthlyCustomerStats(): array;

    /**
     * Get query for new customers table with optional date range filter.
     *
     * @param string|null $from
     * @param string|null $until
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredNewCustomersQuery(?string $from = null, ?string $until = null): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder;

    /**
     * Apply date range filter to new customers query.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string|null $from
     * @param string|null $until
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function applyNewCustomersFilter($query, ?string $from = null, ?string $until = null);

    /**
     * Get query for new customers table.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function getNewCustomersQuery(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder;
}
