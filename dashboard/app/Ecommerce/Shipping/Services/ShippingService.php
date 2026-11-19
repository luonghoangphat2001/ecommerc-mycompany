<?php

namespace App\Ecommerce\Shipping\Services;

use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface;
use App\Ecommerce\Shipping\Services\Drivers\CarrierDriver;
use App\Ecommerce\Shipping\Services\Drivers\FlatRateDriver;
use App\Ecommerce\Shipping\Services\Drivers\FreeShippingDriver;
use App\Ecommerce\Shipping\Services\Drivers\WeightBasedDriver;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ShippingService implements ShippingServiceInterface
{
    protected array $driverClasses = [
        'flat_rate' => FlatRateDriver::class,
        'free_shipping' => FreeShippingDriver::class,
        'weight_based' => WeightBasedDriver::class,
        'local_pickup' => FlatRateDriver::class,
        'carrier' => CarrierDriver::class,
    ];

    public function __construct(
        protected ShippingZoneRepositoryInterface $shippingZoneRepository,
        protected CarrierManager $carrierManager,
        protected PackageCalculator $packageCalculator,
        protected FeaturePolicyServiceInterface $featurePolicy,
        protected Container $container
    ) {}

    /**
     * {@inheritDoc}
     */
    public function resolveMatchingZone(
        ?string $country = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $ward = null
    ): ?ShippingZone {
        if (! $this->featurePolicy->isShippingEnabled()) {
            return null;
        }

        $zone = $this->shippingZoneRepository->findMatchingZone($country, $state, $postcode, $ward);

        if (! $zone) {
            $zone = $this->shippingZoneRepository->all()->first();
        }

        return $zone;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableMethods(
        ?string $country = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $ward = null,
        int $subtotal = 0,
        array $package = []
    ): Collection {
        if (! $this->featurePolicy->isShippingEnabled()) {
            return collect();
        }

        $country = $country ?: 'VN';
        $zone = $this->resolveMatchingZone($country, $state, $postcode, $ward);

        if (! $zone) {
            return collect();
        }

        $destination = [
            'country' => $country,
            'state' => $state,
            'postcode' => $postcode,
            'ward' => $ward,
            'subtotal' => $subtotal,
        ];

        // 1. Calculate manual & configured shipping methods for this zone
        $methods = collect();
        if ($this->featurePolicy->isManualShippingEnabled()) {
            $methods = $zone->methods()->where('is_enabled', true)->get()->map(function (ShippingMethod $method) use ($subtotal, $destination, $package, $zone) {
                $type = $method->type instanceof \BackedEnum ? $method->type->value : (string) $method->type;
                $driverClass = $this->driverClasses[$type] ?? null;

                if (! $driverClass) {
                    return null;
                }

                $driver = $this->container->make($driverClass);
                $options = array_merge(
                    $method->settings ?? [],
                    $package,
                    ['currency' => $method->settings['currency'] ?? 'VND']
                );

                $cost = $driver->calculateFee($subtotal, $destination, $options);
                if ($cost === null || $cost < 0) {
                    return null;
                }

                return [
                    'id' => $method->id,
                    'method_id' => $method->id,
                    'name' => $method->name,
                    'code' => $type,
                    'type' => $type,
                    'cost' => $cost,
                    'price' => $cost,
                    'amount' => $cost,
                    'currency' => $method->settings['currency'] ?? 'VND',
                    'estimated_days' => $method->settings['estimated_days'] ?? null,
                    'description' => $method->description ?? ($method->settings['description'] ?? null),
                    'is_available' => true,
                    'zone_id' => $zone->id,
                    'zone_name' => $zone->name,
                ];
            })->filter()->values();
        }

        // 2. Dynamic Carrier rates if carrier API is enabled
        if ($this->featurePolicy->isCarrierShippingEnabled()) {
            $enabledCarriers = $this->carrierManager->getCarriersForDestination($country, $state);
            foreach ($enabledCarriers as $carrier) {
                if (! $carrier->isConfigured()) {
                    continue;
                }

                $services = $carrier->getAvailableServices($destination, $package);
                foreach ($services as $service) {
                    $serviceCode = (string) ($service['service_id'] ?? 'standard');
                    $quote = $carrier->calculateRate($serviceCode, $destination, $package);

                    if ($quote && isset($quote['amount'])) {
                        $methods->push([
                            'id' => 'carrier_' . $carrier->getId() . '_' . $serviceCode,
                            'method_id' => 'carrier_' . $carrier->getId() . '_' . $serviceCode,
                            'carrier' => $carrier->getId(),
                            'carrier_name' => $carrier->getName(),
                            'name' => $carrier->getName() . ' - ' . ($service['service_name'] ?? $quote['service_name']),
                            'code' => 'carrier_' . $carrier->getId(),
                            'type' => 'carrier',
                            'cost' => (int) $quote['amount'],
                            'price' => (int) $quote['amount'],
                            'amount' => (int) $quote['amount'],
                            'currency' => $quote['currency'] ?? 'VND',
                            'estimated_days' => $quote['estimated_days'] ?? $service['estimated_days'] ?? 2,
                            'description' => 'Báo giá động theo thời gian thực từ hãng vận chuyển.',
                            'is_available' => true,
                            'zone_id' => $zone->id,
                            'zone_name' => $zone->name,
                        ]);
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * {@inheritDoc}
     */
    public function calculateMethodFee(
        ShippingMethod | int | string $method,
        array $destination,
        int $subtotal = 0,
        array $package = []
    ): ?int {
        if (! $this->featurePolicy->isShippingEnabled()) {
            return null;
        }
        if (! $method instanceof ShippingMethod) {
            if (is_string($method) && str_starts_with($method, 'carrier_')) {
                // Parse dynamic carrier
                $parts = explode('_', $method, 3);
                $carrierId = $parts[1] ?? 'ghn';
                $serviceCode = $parts[2] ?? 'standard';
                $carrier = $this->carrierManager->getAdapter($carrierId);
                if ($carrier && $carrier->isEnabled()) {
                    $quote = $carrier->calculateRate($serviceCode, $destination, $package);

                    return $quote ? (int) $quote['amount'] : null;
                }

                return null;
            }

            $method = $this->resolveMethod($method);
        }

        if (! $method || ! $method->is_enabled) {
            return null;
        }

        $type = $method->type instanceof \BackedEnum ? $method->type->value : (string) $method->type;
        $driverClass = $this->driverClasses[$type] ?? null;
        if (! $driverClass) {
            return null;
        }

        $driver = $this->container->make($driverClass);
        $options = array_merge(
            $method->settings ?? [],
            $package,
            ['currency' => $method->settings['currency'] ?? 'VND']
        );

        $fee = $driver->calculateFee($subtotal, $destination, $options);

        return ($fee !== null && $fee >= 0) ? $fee : null;
    }

    public function resolveMethod(ShippingMethod | int | string | null $method): ?ShippingMethod
    {
        if ($method instanceof ShippingMethod) {
            return $method;
        }

        if ($method === null || $method === '') {
            return null;
        }

        $query = ShippingMethod::query()->where('is_enabled', true);

        if (is_numeric($method)) {
            return $query->whereKey((int) $method)->first();
        }

        return $query
            ->where(fn ($builder) => $builder->where('type', $method)->orWhere('name', $method))
            ->first();
    }

    public function resolveCheckoutMethod(?ShippingZone $zone, int | string | null $method): ?ShippingMethod
    {
        if ($zone) {
            $query = $zone->methods()->where('is_enabled', true);

            if (is_numeric($method)) {
                $resolved = (clone $query)->whereKey((int) $method)->first();
                if ($resolved) {
                    return $resolved;
                }
            }

            if ($method !== null && $method !== '') {
                $resolved = (clone $query)
                    ->where(fn ($builder) => $builder
                        ->where('type', $method)
                        ->orWhere('name', $method))
                    ->first();

                if ($resolved) {
                    return $resolved;
                }
            }

            $resolved = $query->first();
            if ($resolved) {
                return $resolved;
            }
        }

        if (is_numeric($method)) {
            $resolved = $this->resolveMethod((int) $method);
            if ($resolved) {
                return $resolved;
            }
        }

        return $this->resolveMethod(is_string($method) ? $method : null)
            ?? $this->resolveMethod(null);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingZoneTableQuery(): Builder
    {
        return $this->shippingZoneRepository->query();
    }

    /**
     * {@inheritDoc}
     */
    public function validateAddress(array $addressData): array
    {
        $errors = [];
        $requiredFields = ['first_name', 'last_name', 'phone', 'address_detail', 'country_code'];
        foreach ($requiredFields as $field) {
            if (empty($addressData[$field])) {
                $errors[$field] = "Trường {$field} là bắt buộc.";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getLocationInfo(string $postcode): ?array
    {
        return null;
    }
}
