<?php

namespace App\Ecommerce\Analytics\Contracts;

interface AnalyticsRepositoryInterface
{
    /**
     * Get monthly revenue data based on filter.
     *
     * @param string $filter
     * @return array
     */
    public function getMonthlyRevenue(string $filter): array;

    /**
     * Get monthly customer registrations based on filter.
     *
     * @param string $filter
     * @return array
     */
    public function getMonthlyCustomerRegistrations(string $filter): array;

    /**
     * Get monthly order status counts for a specific year.
     *
     * @param int $year
     * @return array
     */
    public function getMonthlyOrderStatuses(int $year): array;

    /**
     * Get statistics summary for dashboard.
     *
     * @param array|null $filters
     * @return array
     */
    public function getStatsSummary(?array $filters = []): array;

    /**
     * Get top selling products.
     *
     * @param int $limit
     * @param string $locale
     * @return array
     */
    public function getTopProducts(int $limit = 6, string $locale = 'en'): array;

    /**
     * Get top customers by revenue.
     *
     * @param int $limit
     * @return array
     */
    public function getTopCustomers(int $limit = 10): array;

    /**
     * Get recent customers.
     *
     * @param int $limit
     * @return array
     */
    public function getRecentCustomers(int $limit = 10): array;

    /**
     * Get order status distribution for chart.
     *
     * @return array
     */
    public function getOrderStatusDistribution(): array;

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

    /**
     * Get top products chart data.
     *
     * @param int $limit
     * @param string $locale
     * @return array
     */
    public function getTopProductsChartData(int $limit = 10, string $locale = 'en'): array;
}
