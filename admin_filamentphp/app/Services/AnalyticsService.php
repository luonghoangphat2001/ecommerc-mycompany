<?php

namespace App\Services;

use App\Contracts\Repositories\AnalyticsRepositoryInterface;
use App\Contracts\Services\AnalyticsServiceInterface;

class AnalyticsService implements AnalyticsServiceInterface
{
    /**
     * @var AnalyticsRepositoryInterface
     */
    protected $analyticsRepository;

    /**
     * AnalyticsService constructor.
     *
     * @param AnalyticsRepositoryInterface $analyticsRepository
     */
    public function __construct(AnalyticsRepositoryInterface $analyticsRepository)
    {
        $this->analyticsRepository = $analyticsRepository;
    }

    /**
     * @inheritDoc
     */
    public function getRevenueChartData(string $filter): array
    {
        $raw = $this->analyticsRepository->getMonthlyRevenue($filter);
        return $this->formatMonthlyData($raw);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerChartData(string $filter): array
    {
        $raw = $this->analyticsRepository->getMonthlyCustomerRegistrations($filter);
        return $this->formatMonthlyData($raw);
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusChartData(int $year): array
    {
        return $this->analyticsRepository->getMonthlyOrderStatuses($year);
    }

    /**
     * @inheritDoc
     */
    public function getStatsSummary(?array $filters = []): array
    {
        return $this->analyticsRepository->getStatsSummary($filters ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getTopProducts(int $limit = 6, string $locale = 'en'): array
    {
        return $this->analyticsRepository->getTopProducts($limit, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getTopCustomers(int $limit = 10): array
    {
        return $this->analyticsRepository->getTopCustomers($limit);
    }

    /**
     * @inheritDoc
     */
    public function getRecentCustomers(int $limit = 10): array
    {
        return $this->analyticsRepository->getRecentCustomers($limit);
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatusDistribution(): array
    {
        return $this->analyticsRepository->getOrderStatusDistribution();
    }

    /**
     * @inheritDoc
     */
    public function getFilteredNewCustomersQuery(?string $from = null, ?string $until = null): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->analyticsRepository->getFilteredNewCustomersQuery($from, $until);
    }

    /**
     * @inheritDoc
     */
    public function applyNewCustomersFilter($query, ?string $from = null, ?string $until = null)
    {
        return $this->analyticsRepository->applyNewCustomersFilter($query, $from, $until);
    }

    /**
     * @inheritDoc
     */
    public function getNewCustomersQuery(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $this->analyticsRepository->getNewCustomersQuery();
    }

    /**
     * @inheritDoc
     */
    public function getTopProductsChartData(int $limit = 10, string $locale = 'en'): array
    {
        return $this->analyticsRepository->getTopProductsChartData($limit, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getTopProductsDonutChartData(int $limit = 5, string $locale = 'en'): array
    {
        return $this->analyticsRepository->getTopProductsDonutChartData($limit, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getMonthlyCustomerStats(): array
    {
        return $this->analyticsRepository->getMonthlyCustomerStats();
    }

    /**
     * Fill missing months with zero.
     *
     * @param array $data
     * @return array
     */
    protected function formatMonthlyData(array $data): array
    {
        $formatted = array_fill(1, 12, 0);
        foreach ($data as $month => $value) {
            $formatted[(int)$month] = $value;
        }
        return array_values($formatted);
    }
}
