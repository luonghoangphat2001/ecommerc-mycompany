<?php

namespace App\Ecommerce\Settings\Services;

use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Ecommerce\Menu\Services\FooterMenuSyncService;
use App\Models\Setting;
use App\Settings\AdvancedSettings;
use App\Settings\ApiSettings;
use App\Settings\CheckoutSettings;
use App\Settings\CouponSettings;
use App\Settings\CustomSettings;
use App\Settings\DBSettings;
use App\Settings\EcommerceSettings;
use App\Settings\FooterSettings;
use App\Settings\GeneralSettings;
use App\Settings\InventorySettings;
use App\Settings\LoyaltySettings;
use App\Settings\MailSettings;
use App\Settings\MaintenanceSettings;
use App\Settings\MarketingSettings;
use App\Settings\ProductSettings;
use App\Settings\WebhookSettings;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class AdminSettingGroupService
{
    public function __construct(
        private readonly FinancialAuditServiceInterface $audit,
        private readonly CheckoutSettings $checkout,
        private readonly MarketingSettings $marketing,
        private readonly CouponSettings $coupon,
        private readonly LoyaltySettings $loyalty,
        private readonly FooterMenuSyncService $footerSync,
        private readonly Container $container,
    ) {}

    public function update(string $group, array $schema, array $submitted, Request $request): void
    {
        $settingsClass = $this->settingsClassForGroup($group);
        $financialGroups = ['ecommerce', 'checkout', 'inventory', 'marketing', 'coupon', 'loyalty', 'settings'];

        if ($settingsClass) {
            $settings = $this->container->make($settingsClass);
            foreach ($schema['fields'] as $name => $field) {
                if (($field['type'] ?? 'text') === 'password'
                    && blank($submitted[$name] ?? null)
                    && ! $request->hasFile("settings.$name")) {
                    continue;
                }

                $current = data_get($settings, $name);
                $value = $this->normalize($submitted[$name] ?? null, $field, $request, $group, $name, $current);
                if (in_array($group, $financialGroups, true) && $current !== $value) {
                    $this->audit->logSettingChange($group, $name, $current, $value);
                }
                data_set($settings, $name, $value);
            }
            $settings->save();
        } else {
            foreach ($schema['fields'] as $name => $field) {
                if (($field['type'] ?? 'text') === 'password' && blank($submitted[$name] ?? null)) {
                    continue;
                }

                $existing = Setting::query()->where('group', $group)->where('name', $name)->first();
                $current = $existing?->payload;
                $value = $this->normalize($submitted[$name] ?? null, $field, $request, $group, $name, $current);
                if (in_array($group, $financialGroups, true) && $current !== $value) {
                    $this->audit->logSettingChange($group, $name, $current, $value);
                }

                Setting::updateOrCreate(
                    ['group' => $group, 'name' => $name],
                    ['payload' => $value, 'locked' => false]
                );
            }
        }

        $this->syncRelated($group, $submitted);
    }

    public function values(array $schema): array
    {
        $values = [];
        foreach ($schema as $group => $section) {
            $class = $this->settingsClassForGroup($group);
            if ($class) {
                $settings = $this->container->make($class);
                $values[$group] = method_exists($settings, 'toArray') ? $settings->toArray() : get_object_vars($settings);
            } else {
                $values[$group] = Setting::query()->where('group', $group)->get(['name', 'payload'])->pluck('payload', 'name')->toArray();
            }
        }

        return $values;
    }

    private function syncRelated(string $group, array $submitted): void
    {
        if ($group === 'ecommerce') {
            foreach (['tax_enabled' => 'enable_tax', 'shipping_enabled' => 'enable_shipping'] as $input => $property) {
                if (array_key_exists($input, $submitted)) {
                    $this->checkout->{$property} = (bool) $submitted[$input];
                }
            }
            $this->checkout->save();
        }

        if ($group === 'loyalty') {
            if (array_key_exists('enabled', $submitted)) {
                $this->marketing->loyalty_enabled = (bool) $submitted['enabled'];
            }
            if (array_key_exists('point_conversion_rate', $submitted)) {
                $this->marketing->point_conversion_rate = (int) $submitted['point_conversion_rate'];
            }
            $this->marketing->save();
        }

        if ($group === 'marketing') {
            if (array_key_exists('enable_coupons', $submitted)) {
                $this->coupon->enable_coupons = (bool) $submitted['enable_coupons'];
                $this->coupon->save();
            }
            if (array_key_exists('loyalty_enabled', $submitted)) {
                $this->loyalty->enabled = (bool) $submitted['loyalty_enabled'];
                if (array_key_exists('point_conversion_rate', $submitted)) {
                    $this->loyalty->point_conversion_rate = (int) $submitted['point_conversion_rate'];
                }
                $this->loyalty->save();
            }
        }

        if ($group === 'footer') {
            $this->footerSync->syncFromHandle($submitted['menu_handle'] ?? null);
        }
    }

    private function normalize(mixed $value, array $field, ?Request $request, ?string $group, ?string $name, mixed $current = null): mixed
    {
        return match ($field['type'] ?? 'text') {
            'image' => $this->storeFile($request, $group, $name, $current),
            'boolean' => (bool) $value,
            'number' => $value === null || $value === '' ? null : (int) $value,
            'decimal' => $value === null || $value === '' ? null : (float) $value,
            'payment_gateways', 'checkboxes', 'multiselect', 'multi_select', 'array' => is_array($value) ? array_values(array_filter($value)) : ($this->decode($value) ?: []),
            'exchange_rates' => is_array($value) ? array_filter($value, 'is_numeric') : ($this->decode($value) ?: []),
            'json' => $this->decode($value),
            default => $value === '' ? null : $value,
        };
    }

    private function storeFile(?Request $request, ?string $group, ?string $name, mixed $current): mixed
    {
        $file = $request && $group && $name ? $request->file("settings.$name") : null;

        return $file ? $file->storePublicly("settings/$group", 'public') : $current;
    }

    private function decode(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function settingsClassForGroup(string $group): ?string
    {
        return match ($group) {
            'settings' => DBSettings::class, 'general' => GeneralSettings::class, 'ecommerce' => EcommerceSettings::class,
            'products' => ProductSettings::class, 'checkout' => CheckoutSettings::class, 'inventory' => InventorySettings::class,
            'loyalty' => LoyaltySettings::class, 'marketing' => MarketingSettings::class, 'footer' => FooterSettings::class,
            'mail' => MailSettings::class, 'webhook' => WebhookSettings::class, 'api' => ApiSettings::class,
            'advanced' => AdvancedSettings::class, 'custom' => CustomSettings::class, 'maintenance' => MaintenanceSettings::class,
            default => null,
        };
    }
}
